<?php

namespace App\Services\Hungary;

use App\Models\Order;
use App\Models\Vendor;
use App\Services\VendorCountry\InvoiceServiceInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class NAVService implements InvoiceServiceInterface
{
    public function generate(Order $order)
    {
        $vendor = $order->vendor;
        $token = $this->generateRequestToken($vendor);
        $xml = $this->generateXML($order);
        $response = $this->uploadInvoiceXML($xml, $vendor, $token);

        $storageDir = "invoices/hu/{$order->id}";
        Storage::disk('private')->put("{$storageDir}/invoice.xml", $xml);
        Storage::disk('private')->put("{$storageDir}/response.json", json_encode($response));
        $order->invoice_type = 'nav';
        $order->invoice_storage_path = "{$storageDir}/invoice.xml";
        $order->save();

        $this->handleNAVResponse($response, $order);
    }

    public function generateStorno(Order $order, Order $refundOrder)
    {
        $vendor = $order->vendor;
        $token = $this->generateRequestToken($vendor);

        $xml = $this->generateXML($refundOrder, $order);
        $response = $this->uploadInvoiceXML($xml, $vendor, $token);

        $storageDir = "invoices/hu/{$refundOrder->id}";
        Storage::disk('private')->put("{$storageDir}/invoice.xml", $xml);
        Storage::disk('private')->put("{$storageDir}/response.json", json_encode($response));
        $refundOrder->invoice_type = 'nav';
        $refundOrder->invoice_storage_path = "{$storageDir}/invoice.xml";

        $refundOrder->save();

        $this->handleNAVResponse($response, $refundOrder);
    }

    private function handleNAVResponse($response, Order $order)
    {
        if (isset($response['result']['errorCode'])) {
            $order->nav_status = 'rejected';
            $order->nav_response = json_encode($response);
        } else {
            $order->nav_status = 'accepted';
            $order->nav_invoice_id = $response['transactionId'];
        }

        $order->save();
    }

    public function generateXML(Order $order, ?Order $originalOrder = null)
    {
        $xml = new \SimpleXMLElement('<Invoice/>' );
        $xml->addChild('order_id', $order->id);
        if ($originalOrder) {
            $xml->addChild('original_order_id', $originalOrder->id);
            $xml->addChild('invoice_type', 'STORNO');
        } else {
            $xml->addChild('invoice_type', 'ORIGINAL');
        }

        $supplier = $xml->addChild('supplier');
        $supplier->addChild('name', $order->vendor->store_name);

        $customer = $xml->addChild('customer');
        $customer->addChild('name', $order->user->name);

        $items = $xml->addChild('items');
        foreach ($order->orderItems as $item) {
            $line = $items->addChild('item');
            $line->addChild('description', $item->product->name ?? 'Item');
            $line->addChild('quantity', $item->quantity);
            $line->addChild('unit_price', $item->gross_price);
        }

        $xml->addChild('total', $order->total_price);

        return $xml->asXML();
    }

    public function generateRequestToken(Vendor $vendor)
    {
        $payload = [
            'user' => $vendor->nav_user_id,
            'password' => $vendor->nav_exchange_key,
        ];

        $body = json_encode($payload);
        $signature = $this->requestSignature($body);

        try {
            $response = Http::retry(3, 1000)->withHeaders([
                'Content-Type' => 'application/json',
                'Request-Signature' => $signature,
            ])->post('https://api-test.onlineszamla.nav.gov.hu/invoiceService/v3/tokenExchange', $payload);

            if ($response->successful()) {
                $data = $response->json();
                return $data['encodedExchangeToken'] ?? '';
            }
        } catch (\Throwable $e) {
            // Ignore and return empty string
        }

        return '';
    }

    private function requestSignature(string $payload): string
    {
        return base64_encode(hash('sha3-512', $payload, true));
    }

    public function uploadInvoiceXML($xml, Vendor $vendor, $token)
    {
        $payload = [
            'user' => $vendor->nav_user_id,
            'password' => $vendor->nav_exchange_key,
            'invoice' => base64_encode($xml),
        ];

        $body = json_encode($payload);
        $signature = $this->requestSignature($body);

        try {
            $response = Http::retry(3, 1000)->withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
                'Request-Signature' => $signature,
            ])->post('https://api-test.onlineszamla.nav.gov.hu/invoiceService/v3/manageInvoice', $payload);

            if ($response->successful()) {
                return $response->json();
            }

            return ['success' => false, 'message' => $response->body()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

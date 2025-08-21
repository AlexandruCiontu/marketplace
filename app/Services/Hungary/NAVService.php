<?php

namespace App\Services\Hungary;

use App\Models\Order;
use App\Models\Vendor;
use App\Services\VendorCountry\InvoiceServiceInterface;
use Illuminate\Support\Facades\Storage;

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
        $xml = $this->generateXML($refundOrder);
        $response = $this->uploadInvoiceXML($xml, $vendor, $token);

        $storageDir = "invoices/hu/{$refundOrder->id}";
        Storage::disk('private')->put("{$storageDir}/storno.xml", $xml);
        Storage::disk('private')->put("{$storageDir}/response.json", json_encode($response));
        $refundOrder->invoice_type = 'nav-storno';
        $refundOrder->invoice_storage_path = "{$storageDir}/storno.xml";
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

    public function generateXML(Order $order)
    {
        // This is a placeholder. A real implementation would use a library to generate the NAV-specific XML.
        $xml = new \SimpleXMLElement('<Invoice/>');
        $xml->addChild('order_id', $order->id);
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

        $ch = curl_init('https://api-test.onlineszamla.nav.gov.hu/invoiceService/v3/tokenExchange');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Request-Signature: ' . $signature,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        return $data['encodedExchangeToken'] ?? '';
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

        $ch = curl_init('https://api-test.onlineszamla.nav.gov.hu/invoiceService/v3/manageInvoice');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            'Request-Signature: ' . $signature,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}

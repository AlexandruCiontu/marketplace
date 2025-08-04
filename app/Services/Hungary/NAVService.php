<?php

namespace App\Services\Hungary;

use App\Models\Order;
use App\Services\VendorCountry\InvoiceServiceInterface;

class NAVService implements InvoiceServiceInterface
{
    public function generate(Order $order)
    {
        $vendor = $order->vendor;
        $token = $this->generateRequestToken($vendor);
        $xml = $this->generateXML($order);
        $response = $this->uploadInvoiceXML($xml, $vendor, $token);

        $this->handleNAVResponse($response, $order);
    }

    public function generateStorno(Order $order, Order $refundOrder)
    {
        // TODO: Implement this method
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

        $ch = curl_init('https://api-test.onlineszamla.nav.gov.hu/invoiceService/v3/tokenExchange');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        return $data['encodedExchangeToken'];
    }

    public function uploadInvoiceXML($xml, Vendor $vendor, $token)
    {
        $payload = [
            'user' => $vendor->nav_user_id,
            'password' => $vendor->nav_exchange_key,
            'invoice' => base64_encode($xml),
        ];

        $ch = curl_init('https://api-test.onlineszamla.nav.gov.hu/invoiceService/v3/manageInvoice');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}

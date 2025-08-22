<?php

namespace App\Services\VendorCountry\HU;

use App\Models\Order;
use App\Services\VendorCountry\InvoiceServiceInterface;
use Illuminate\Support\Facades\Log;

class InvoiceService implements InvoiceServiceInterface
{
    public function __construct(private NavClient $navClient)
    {
    }

    public function generate(Order $order)
    {
        // 1. Get vendor's NAV keys
        $vendorKeys = [
            'user_id' => $order->vendor->nav_user_id,
            'exchange_key' => $order->vendor->nav_exchange_key,
        ];

        // 2. Generate NAV XML for the order
        $navXml = $this->generateNavXml($order);

        // 3. Send to NAV Online
        $response = $this->navClient->send($navXml, $vendorKeys);

        // 4. Save invoice details and NAV response
        Log::info("Generated HU NAV Invoice for order {$order->id}", ['response' => $response]);

        return $response;
    }

    public function generateStorno(Order $order, Order $refundOrder)
    {
        // 1. Vendor keys remain the same as the original order
        $vendorKeys = [
            'user_id' => $order->vendor->nav_user_id,
            'exchange_key' => $order->vendor->nav_exchange_key,
        ];

        // 2. Generate a NAV storno XML referencing the original invoice
        $navXml = $this->generateNavXml($refundOrder, $order);

        // 3. Send to NAV Online
        $response = $this->navClient->send($navXml, $vendorKeys);

        Log::info("Generated HU Storno for order {$order->id}", ['response' => $response]);

        return $response;
    }

    private function generateNavXml(Order $order, ?Order $originalOrder = null): string
    {
        $xml = new \SimpleXMLElement('<Invoice/>' );
        $xml->addChild('invoiceNumber', $order->id);
        if ($originalOrder) {
            $xml->addChild('originalInvoiceNumber', $originalOrder->id);
            $xml->addChild('invoiceType', 'STORNO');
        } else {
            $xml->addChild('invoiceType', 'ORIGINAL');
        }

        // Supplier
        $supplier = $xml->addChild('Supplier');
        $supplier->addChild('Name', $order->vendor->store_name);

        // Customer
        $customer = $xml->addChild('Customer');
        $customer->addChild('Name', $order->user->name);

        // Items
        $items = $xml->addChild('Items');
        foreach ($order->orderItems as $item) {
            $line = $items->addChild('Item');
            $line->addChild('Description', $item->product->name ?? 'Item');
            $line->addChild('Quantity', $item->quantity);
            $line->addChild('UnitPrice', $item->gross_price);
        }
        $xml->addChild('Total', $order->total_price);

        return $xml->asXML();
    }
}

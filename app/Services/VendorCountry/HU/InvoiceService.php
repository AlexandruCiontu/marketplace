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
        Log::info("Generated HU Storno for order {$order->id}");
    }

    private function generateNavXml(Order $order): string
    {
        // Placeholder for NAV XML generation logic
        return '<NAV-Invoice>...</NAV-Invoice>';
    }
}

<?php

namespace App\Services\Hungary;

use App\Models\Order;
use App\Services\VendorCountry\InvoiceServiceInterface;

class NAVService implements InvoiceServiceInterface
{
    public function generate(Order $order)
    {
        // TODO: Implement this method
    }

    public function generateStorno(Order $order, Order $refundOrder)
    {
        // TODO: Implement this method
    }

    public function generateRequestToken()
    {
        // TODO: Implement this method
    }

    public function uploadInvoiceXML($xml)
    {
        // TODO: Implement this method
    }
}

<?php

namespace App\Services\Romania;

use App\Models\Order;
use App\Services\VendorCountry\InvoiceServiceInterface;

class ANAFService implements InvoiceServiceInterface
{
    public function generate(Order $order)
    {
        // TODO: Implement this method
    }

    public function generateStorno(Order $order, Order $refundOrder)
    {
        // TODO: Implement this method
    }

    public function generateXML($order)
    {
        // TODO: Implement this method
    }

    public function signWithCertificate($xml)
    {
        // TODO: Implement this method
    }

    public function uploadToANAF($xml)
    {
        // TODO: Implement this method
    }
}

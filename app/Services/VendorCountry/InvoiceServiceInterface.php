<?php

namespace App\Services\VendorCountry;

use App\Models\Order;

interface InvoiceServiceInterface
{
    /**
     * Generates an invoice for a specific order.
     *
     * @param Order $order The order for which the invoice is generated.
     * @return mixed May return a file path, a response object, etc.
     */
    public function generate(Order $order);

    /**
     * Generates a credit note for an order.
     *
     * @param Order $order The original order.
     * @param Order $refundOrder The refund order.
     * @return mixed
     */
    public function generateStorno(Order $order, Order $refundOrder);
}

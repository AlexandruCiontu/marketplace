<?php

namespace App\Services\VendorCountry;

use App\Models\Order;

interface InvoiceServiceInterface
{
    /**
     * Generează o factură pentru o comandă specifică.
     *
     * @param Order $order Comanda pentru care se generează factura.
     * @return mixed Poate returna calea către fișier, un obiect de răspuns, etc.
     */
    public function generate(Order $order);

    /**
     * Generează o factură de stornare pentru o comandă.
     *
     * @param Order $order Comanda originală.
     * @param Order $refundOrder Comanda de stornare.
     * @return mixed
     */
    public function generateStorno(Order $order, Order $refundOrder);
}

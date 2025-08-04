<?php

namespace App\Services\VendorCountry\BG;

use App\Models\Order;
use App\Services\VendorCountry\InvoiceServiceInterface;
use Illuminate\Support\Facades\Log;

class InvoiceService implements InvoiceServiceInterface
{
    public function __construct(private PdfService $pdfService)
    {
    }

    public function generate(Order $order)
    {
        // 1. Generate PDF for the order
        $pdfPath = $this->pdfService->generate($order);

        // 2. Save invoice details
        Log::info("Generated BG PDF Invoice for order {$order->id} at path: {$pdfPath}");

        return ['success' => true, 'path' => $pdfPath];
    }

    public function generateStorno(Order $order, Order $refundOrder)
    {
        Log::info("Generated BG Storno for order {$order->id}");
    }
}

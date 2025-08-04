<?php

namespace App\Services\VendorCountry\BG;

use App\Models\Order;
use Illuminate\Support\Facades\Storage;

class PdfService
{
    /**
     * Generates a PDF for the given order and saves it.
     *
     * @param Order $order
     * @return string The path to the saved PDF.
     */
    public function generate(Order $order): string
    {
        // Placeholder for PDF generation logic.
        // This would use a library like DomPDF or Snappy to convert a Blade view to a PDF.
        $pdfContent = "<h1>Invoice for Order #{$order->id}</h1><p>Total: {$order->total_price}</p>";
        $filename = "invoices/bg-{$order->id}-".date('Y-m-d').'.pdf';

        Storage::put($filename, $pdfContent);

        return $filename;
    }
}

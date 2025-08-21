<?php

namespace App\Services\VendorCountry\BG;

use App\Models\Order;
use App\Services\VendorCountry\InvoiceServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InvoiceService implements InvoiceServiceInterface
{
    public function __construct(private PdfService $pdfService, private NraClient $nraClient)
    {
    }

    public function generate(Order $order)
    {
        $pdfPath = $this->pdfService->generate($order);
        $pdfContent = Storage::get($pdfPath);
        $response = $this->nraClient->send($pdfContent);

        Log::info("Generated BG PDF Invoice for order {$order->id}", ['response' => $response]);

        return $response;
    }

    public function generateStorno(Order $order, Order $refundOrder)
    {

        $pdfPath = $this->pdfService->generate($refundOrder);
        $pdfContent = Storage::get($pdfPath);
        $response = $this->nraClient->send($pdfContent);

        Log::info("Generated BG Storno for order {$order->id}", ['response' => $response]);

        return $response;
    }
}

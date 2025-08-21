<?php

namespace App\Services\VendorCountry\RO;

use App\Models\Order;
use App\Services\Fiscal\UblGeneratorService;
use App\Services\VendorCountry\InvoiceServiceInterface;
use Illuminate\Support\Facades\Log;

class InvoiceService implements InvoiceServiceInterface
{
    public function __construct(private AnafClient $anafClient)
    {
    }

    public function generate(Order $order)
    {
        // 1. Generate UBL XML for the order
        $xml = $this->generateUblXml($order);

        // 2. Sign the XML with the vendor's .pfx certificate
        $signedXml = $this->signXml($xml, $order->vendor);

        // 3. Send to ANAF e-Factura
        $response = $this->anafClient->send($signedXml);

        // 4. Save invoice details and ANAF response
        // This will be implemented later.
        Log::info("Generated RO e-Factura for order {$order->id}", ['response' => $response]);

        return $response;
    }

    public function generateStorno(Order $order, Order $refundOrder)
    {
        // 1. Generate credit note XML referencing the original order
        $xml = $this->generateCreditNoteXml($order, $refundOrder);

        // 2. Sign and send to ANAF
        $signedXml = $this->signXml($xml, $order->vendor);
        $response = $this->anafClient->send($signedXml);

        Log::info("Generated RO Storno for order {$order->id}", ['response' => $response]);

        return $response;
    }

    private function generateUblXml(Order $order): string
    {
        $generator = new UblGeneratorService();
        return $generator->generateInvoice($order);
    }

    private function generateCreditNoteXml(Order $order, Order $refundOrder): string
    {
        $generator = new UblGeneratorService();
        return $generator->generateCreditNote($order, $refundOrder);
    }

    private function signXml(string $xml, \App\Models\Vendor $vendor): string
    {
        // Placeholder for XML signing logic using the vendor's .pfx certificate
        // This would involve retrieving the certificate path and password from the vendor's settings
        return '<Signed-UBL-Invoice>...</Signed-UBL-Invoice>';
    }
}

<?php

namespace App\Services\VendorCountry\RO;

use App\Models\Order;
use App\Services\Fiscal\UblGeneratorService;
use App\Services\VendorCountry\InvoiceServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
        $pfxPath = Storage::disk('private')->path($vendor->anaf_pfx_path);
        $password = $vendor->anaf_certificate_password;

        $pkcs12 = file_get_contents($pfxPath);
        $certs = [];
        if (!openssl_pkcs12_read($pkcs12, $certs, $password)) {
            throw new \RuntimeException('Unable to read PKCS#12 certificate.');
        }

        $in = tmpfile();
        fwrite($in, $xml);
        $inPath = stream_get_meta_data($in)['uri'];
        $out = tmpfile();
        $outPath = stream_get_meta_data($out)['uri'];

        $result = openssl_pkcs7_sign(
            $inPath,
            $outPath,
            $certs['cert'],
            $certs['pkey'],
            [],
            PKCS7_BINARY | PKCS7_DETACHED
        );

        if (!$result) {
            fclose($in);
            fclose($out);
            throw new \RuntimeException('Failed to sign XML.');
        }

        $signed = file_get_contents($outPath);

        fclose($in);
        fclose($out);

        $matches = [];
        if (preg_match('/-----BEGIN PKCS7-----(.*)-----END PKCS7-----/s', $signed, $matches)) {
            return base64_decode($matches[1]);
        }

        return $signed;
    }
}

<?php

namespace App\Services\Romania;

use App\Models\Order;
use App\Models\Vendor;
use App\Services\Fiscal\UblGeneratorService;
use App\Services\VendorCountry\InvoiceServiceInterface;

use Illuminate\Support\Facades\Storage;
use SoapClient;
use SoapHeader;
use SoapVar;

class ANAFService implements InvoiceServiceInterface
{
    public function generate(Order $order)
    {
        $vendor = $order->vendor;
        $this->convertPfxToPem($vendor);

        $xml = $this->generateXML($order);
        $signedXml = $this->signWithCertificate($xml, $vendor);

        try {
            $response = $this->uploadToANAF($signedXml, $vendor);

            $storageDir = "invoices/ro/{$order->id}";
            Storage::disk('private')->put("{$storageDir}/invoice.xml", $signedXml);
            Storage::disk('private')->put("{$storageDir}/response.xml", is_string($response) ? $response : json_encode($response));
            $order->invoice_type = 'anaf';
            $order->invoice_storage_path = "{$storageDir}/invoice.xml";
            $order->save();

            $this->handleANAFResponse($response, $order);
        } catch (\Throwable $e) {
            $failedPath = "invoices/failed/order_{$order->id}.xml";
            Storage::disk('private')->put($failedPath, $signedXml);
            $order->invoice_type = 'failed';
            $order->invoice_storage_path = $failedPath;
            $order->save();
        }
    }

    private function handleANAFResponse($response, Order $order)
    {
        if (isset($response->Error)) {
            $order->efactura_status = 'rejected';
            $order->efactura_message = $response->Error;
        } else {
            $order->efactura_status = 'accepted';
            $order->efactura_sent_at = now();
        }

        $order->save();
    }

    public function generateStorno(Order $order, Order $refundOrder)
    {
        $vendor = $order->vendor;
        $this->convertPfxToPem($vendor);

        $xml = $this->generateXML($refundOrder);
        $signedXml = $this->signWithCertificate($xml, $vendor);

        try {
            $response = $this->uploadToANAF($signedXml, $vendor);

            $storageDir = "invoices/ro/{$refundOrder->id}";
            Storage::disk('private')->put("{$storageDir}/storno.xml", $signedXml);
            Storage::disk('private')->put("{$storageDir}/response.xml", is_string($response) ? $response : json_encode($response));
            $refundOrder->invoice_type = 'anaf-storno';
            $refundOrder->invoice_storage_path = "{$storageDir}/storno.xml";
            $refundOrder->save();

            $this->handleANAFResponse($response, $refundOrder);
        } catch (\Throwable $e) {
            $failedPath = "invoices/failed/order_{$refundOrder->id}_storno.xml";
            Storage::disk('private')->put($failedPath, $signedXml);
            $refundOrder->invoice_type = 'failed';
            $refundOrder->invoice_storage_path = $failedPath;
            $refundOrder->save();
        }
    }

    private function convertPfxToPem(Vendor $vendor)
    {
        $pfxPath = Storage::disk('private')->path($vendor->anaf_pfx_path);
        $password = $vendor->anaf_certificate_password;
        $vendorDir = "vendors/{$vendor->user_id}";
        $pemPath = Storage::disk('private')->path("{$vendorDir}/cert.pem");
        $keyPath = Storage::disk('private')->path("{$vendorDir}/key.pem");

        Storage::disk('private')->makeDirectory($vendorDir);

        $pemCommand = "openssl pkcs12 -in {$pfxPath} -out {$pemPath} -clcerts -nokeys -passin pass:{$password}";
        $keyCommand = "openssl pkcs12 -in {$pfxPath} -out {$keyPath} -nocerts -nodes -passin pass:{$password}";

        shell_exec($pemCommand);
        shell_exec($keyCommand);
    }

    public function generateXML(Order $order)
    {
        $generator = new UblGeneratorService();
        return $generator->generateInvoice($order);
    }

    public function signWithCertificate($xml, Vendor $vendor)
    {
        $vendorDir = "vendors/{$vendor->user_id}";
        $keyPath = Storage::disk('private')->path("{$vendorDir}/key.pem");
        $certPath = Storage::disk('private')->path("{$vendorDir}/cert.pem");
        $signedXmlPath = Storage::disk('private')->path("{$vendorDir}/signed.xml");

        $xmlFile = tmpfile();
        fwrite($xmlFile, $xml);
        $xmlPath = stream_get_meta_data($xmlFile)['uri'];

        $command = "openssl smime -sign -binary -in {$xmlPath} -signer {$certPath} -inkey {$keyPath} -out {$signedXmlPath} -outform DER";
        shell_exec($command);

        fclose($xmlFile);

        return file_get_contents($signedXmlPath);
    }

    public function uploadToANAF($xml, Vendor $vendor)
    {
        $nonce = random_bytes(16);
        $created = gmdate('Y-m-d\TH:i:s\Z');
        $passwordDigest = base64_encode(sha1($nonce . $created . $vendor->anaf_password, true));

        $wsse = '<wsse:Security SOAP-ENV:mustUnderstand="1" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd">
            <wsse:UsernameToken>
                <wsse:Username>' . $vendor->anaf_username . '</wsse:Username>
                <wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest">' . $passwordDigest . '</wsse:Password>
                <wsse:Nonce>' . base64_encode($nonce) . '</wsse:Nonce>
                <wsu:Created>' . $created . '</wsu:Created>
            </wsse:UsernameToken>
        </wsse:Security>';

        $client = new SoapClient('https://efactura.anaf.ro/FacturareService-v3/ws/FacturareService?wsdl', [
            'trace' => true,
            'local_cert' => Storage::disk('private')->path("vendors/{$vendor->user_id}/cert.pem"),
            'local_pk' => Storage::disk('private')->path("vendors/{$vendor->user_id}/key.pem"),
        ]);

        $header = new SoapHeader(
            'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd',
            'Security',
            new SoapVar($wsse, XSD_ANYXML),
            true
        );
        $client->__setSoapHeaders([$header]);

        return $client->__soapCall('uploadInvoice', [[
            'fileName' => 'invoice.xml',
            'content' => base64_encode($xml),
        ]]);
    }
}

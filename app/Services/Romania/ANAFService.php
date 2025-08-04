<?php

namespace App\Services\Romania;

use App\Models\Order;
use App\Services\VendorCountry\InvoiceServiceInterface;

use Illuminate\Support\Facades\Storage;

class ANAFService implements InvoiceServiceInterface
{
    public function generate(Order $order)
    {
        $vendor = $order->vendor;
        $this->convertPfxToPem($vendor);

        $xml = $this->generateXML($order);
        $signedXml = $this->signWithCertificate($xml, $vendor);
        $response = $this->uploadToANAF($signedXml, $vendor);

        $this->handleANAFResponse($response, $order);
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
        // TODO: Implement this method
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
        $vendor = $order->vendor;
        $customer = $order->user;

        $supplier = (new \Simplito\UblInvoice\Party())
            ->setName($vendor->store_name)
            ->setCity($vendor->store_address)
            ->setCountry($vendor->country_code)
            ->setCompanyId($vendor->cif);

        $client = (new \Simplito\UblInvoice\Party())
            ->setName($customer->name)
            ->setCity($order->shippingAddress->city)
            ->setCountry($order->shippingAddress->country->code)
            ->setCompanyId($customer->cif ?? '');

        $invoiceLines = [];
        foreach ($order->orderItems as $item) {
            $invoiceLines[] = (new \Simplito\UblInvoice\InvoiceLine())
                ->setId($item->id)
                ->setName($item->product->title)
                ->setPrice($item->price)
                ->setQuantity($item->quantity)
                ->setUnit('buc')
                ->setVatRate($item->product->vat_rate);
        }

        $invoice = (new \Simplito\UblInvoice\Invoice())
            ->setSupplier($supplier)
            ->setClient($client)
            ->setInvoiceLines($invoiceLines);

        return $invoice->asXML();
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

        $command = "openssl smime -sign -in {$xmlPath} -out {$signedXmlPath} -signer {$certPath} -inkey {$keyPath} -passin pass:'' -outform DER";
        shell_exec($command);

        fclose($xmlFile);

        return file_get_contents($signedXmlPath);
    }

    public function uploadToANAF($xml, Vendor $vendor)
    {
        $anafWsdl = 'https://webservicesp.anaf.ro/PlatitorTvaRest/services/PlatitorTvaRest?wsdl';

        $client = new \SoapClient($anafWsdl, [
            'stream_context' => stream_context_create([
                'ssl' => [
                    'local_cert' => Storage::disk('private')->path("vendors/{$vendor->user_id}/cert.pem"),
                    'local_pk' => Storage::disk('private')->path("vendors/{$vendor->user_id}/key.pem"),
                    'passphrase' => '',
                ],
            ]),
        ]);

        $response = $client->upload([
            'file' => base64_encode($xml),
            'cif' => $vendor->cif,
        ]);

        return $response;
    }
}

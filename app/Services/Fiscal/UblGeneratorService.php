<?php

namespace App\Services\Fiscal;

use App\Models\Order;
use DOMDocument;

class UblGeneratorService
{
    /**
     * Generate a minimal UBL 2.1 invoice XML conforming to ANAF requirements.
     */
    public function generateInvoice(Order $order): string
    {
        $vendor = $order->vendor;
        $customer = $order->user;

        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        $invoice = $doc->createElementNS(
            'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
            'Invoice'
        );
        $invoice->setAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $invoice->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $doc->appendChild($invoice);

        $invoice->appendChild($doc->createElement('cbc:CustomizationID', 'RO_EF')); // ANAF extension
        $invoice->appendChild($doc->createElement('cbc:ProfileID', 'EFACTURA'));   // profile
        $invoice->appendChild($doc->createElement('cbc:ID', $order->id));
        $invoice->appendChild($doc->createElement('cbc:IssueDate', $order->created_at->format('Y-m-d')));
        $invoice->appendChild($doc->createElement('cbc:InvoiceTypeCode', '380'));

        // Supplier party
        $supplierParty = $doc->createElement('cac:AccountingSupplierParty');
        $supplier = $doc->createElement('cac:Party');
        $supplierName = $doc->createElement('cac:PartyName');
        $supplierName->appendChild($doc->createElement('cbc:Name', $vendor->store_name));
        $supplier->appendChild($supplierName);
        $supplierParty->appendChild($supplier);
        $invoice->appendChild($supplierParty);

        // Customer party
        $customerParty = $doc->createElement('cac:AccountingCustomerParty');
        $customerNode = $doc->createElement('cac:Party');
        $customerName = $doc->createElement('cac:PartyName');
        $customerName->appendChild($doc->createElement('cbc:Name', $customer->name));
        $customerNode->appendChild($customerName);
        $customerParty->appendChild($customerNode);
        $invoice->appendChild($customerParty);

        return $doc->saveXML();
    }
}

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

        // Line items
        foreach ($order->orderItems as $idx => $item) {
            $line = $doc->createElement('cac:InvoiceLine');
            $line->appendChild($doc->createElement('cbc:ID', $idx + 1));
            $line->appendChild($doc->createElement('cbc:InvoicedQuantity', $item->quantity));
            $line->appendChild($doc->createElement('cbc:LineExtensionAmount', $item->net_price));

            $itemNode = $doc->createElement('cac:Item');
            $itemNode->appendChild($doc->createElement('cbc:Name', $item->product->name ?? 'Item'));
            $line->appendChild($itemNode);

            $price = $doc->createElement('cac:Price');
            $price->appendChild($doc->createElement('cbc:PriceAmount', $item->gross_price));
            $line->appendChild($price);

            $invoice->appendChild($line);
        }

        // Totals
        $totals = $doc->createElement('cac:LegalMonetaryTotal');
        $totals->appendChild($doc->createElement('cbc:PayableAmount', $order->total_price));
        $invoice->appendChild($totals);

        return $doc->saveXML();
    }

    /**
     * Generate a minimal UBL 2.1 credit note XML referencing the original order.
     */
    public function generateCreditNote(Order $originalOrder, Order $refundOrder): string
    {
        $vendor = $originalOrder->vendor;
        $customer = $originalOrder->user;

        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->formatOutput = true;

        $credit = $doc->createElementNS(
            'urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2',
            'CreditNote'
        );
        $credit->setAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $credit->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $doc->appendChild($credit);

        $credit->appendChild($doc->createElement('cbc:ID', 'CN-' . $refundOrder->id));
        $credit->appendChild($doc->createElement('cbc:IssueDate', $refundOrder->created_at->format('Y-m-d')));

        $billingRef = $doc->createElement('cac:BillingReference');
        $invoiceRef = $doc->createElement('cac:InvoiceDocumentReference');
        $invoiceRef->appendChild($doc->createElement('cbc:ID', $originalOrder->id));
        $billingRef->appendChild($invoiceRef);
        $credit->appendChild($billingRef);

        // Supplier party
        $supplierParty = $doc->createElement('cac:AccountingSupplierParty');
        $supplier = $doc->createElement('cac:Party');
        $supplierName = $doc->createElement('cac:PartyName');
        $supplierName->appendChild($doc->createElement('cbc:Name', $vendor->store_name));
        $supplier->appendChild($supplierName);
        $supplierParty->appendChild($supplier);
        $credit->appendChild($supplierParty);

        // Customer party
        $customerParty = $doc->createElement('cac:AccountingCustomerParty');
        $customerNode = $doc->createElement('cac:Party');
        $customerName = $doc->createElement('cac:PartyName');
        $customerName->appendChild($doc->createElement('cbc:Name', $customer->name));
        $customerNode->appendChild($customerName);
        $customerParty->appendChild($customerNode);
        $credit->appendChild($customerParty);

        foreach ($refundOrder->orderItems as $idx => $item) {
            $line = $doc->createElement('cac:CreditNoteLine');
            $line->appendChild($doc->createElement('cbc:ID', $idx + 1));
            $line->appendChild($doc->createElement('cbc:CreditedQuantity', $item->quantity));
            $line->appendChild($doc->createElement('cbc:LineExtensionAmount', $item->net_price));

            $itemNode = $doc->createElement('cac:Item');
            $itemNode->appendChild($doc->createElement('cbc:Name', $item->product->name ?? 'Item'));
            $line->appendChild($itemNode);

            $price = $doc->createElement('cac:Price');
            $price->appendChild($doc->createElement('cbc:PriceAmount', $item->gross_price));
            $line->appendChild($price);

            $credit->appendChild($line);
        }

        $totals = $doc->createElement('cac:LegalMonetaryTotal');
        $totals->appendChild($doc->createElement('cbc:PayableAmount', $refundOrder->total_price));
        $credit->appendChild($totals);

        return $doc->saveXML();
    }
}

<?php

namespace App\Services\VendorCountry\HU;

use Illuminate\Support\Facades\Http;

class NavClient
{
    /**
     * Sends the NAV XML to the NAV Online API.
     *
     * @param string $navXml
     * @param array $vendorKeys
     * @return array
     */
    public function send(string $navXml, array $vendorKeys): array
    {
        $endpoint = 'https://api.nav.gov.hu/v3/invoiceService/manageInvoice';

        try {
            $response = Http::retry(3, 1000)->post($endpoint, [
                'user' => $vendorKeys['user_id'] ?? '',
                'password' => $vendorKeys['exchange_key'] ?? '',
                'invoice' => base64_encode($navXml),
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'transaction_id' => $response->json('transactionId'),
                    'message' => 'Invoice sent to NAV successfully.',
                ];
            }

            return [
                'success' => false,
                'message' => $response->body(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}

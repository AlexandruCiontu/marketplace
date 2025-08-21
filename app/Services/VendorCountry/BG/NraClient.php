<?php

namespace App\Services\VendorCountry\BG;

use Illuminate\Support\Facades\Http;

class NraClient
{
    /**
     * Sends the PDF invoice to the Bulgarian NRA API.
     *
     * @param string $pdfContent
     * @return array
     */
    public function send(string $pdfContent): array
    {
        $endpoint = 'https://api.nra.bg/v1/invoices';

        try {
            $response = Http::retry(3, 1000)->post($endpoint, [
                'file' => base64_encode($pdfContent),
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'Invoice sent to NRA successfully.',
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

<?php

namespace App\Services\VendorCountry\RO;

use Illuminate\Support\Facades\Http;

class AnafClient
{
    /**
     * Sends the signed XML to the ANAF e-Factura API.
     *
     * @param string $signedXml
     * @return array
     */
    public function send(string $signedXml): array
    {
        $endpoint = 'https://api.anaf.ro/prod/FCTEL/rest/upload';

        try {
            $response = Http::retry(3, 1000)->post($endpoint, [
                'file' => base64_encode($signedXml),
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'upload_id' => $response->json('upload_id'),
                    'message' => 'Factura a fost trimisă cu succes.',
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

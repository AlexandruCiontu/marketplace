<?php

namespace App\Helpers;

class VatHelper
{
    /**
     * Cache local pentru ratele TVA.
     */
    protected static array $rates = [];

    /**
     * Returnează rata TVA pentru o țară și un tip specificat.
     *
     * Dacă tipul ales de vendor nu este disponibil pentru acea țară,
     * se folosește automat fallback la rata standard.
     *
     * @param string $countryCode Ex: 'RO', 'DE'
     * @param string $type Ex: 'standard', 'reduced', 'reduced2', 'super_reduced', 'zero'
     * @return float
     */
    public static function getRate(string $countryCode, string $type = 'standard'): float
    {
        try {
            // Încarcă datele o singură dată
            if (empty(self::$rates)) {
                $file = storage_path('app/vat/rates.json');

                if (!file_exists($file)) {
                    return 0.0;
                }

                $json = file_get_contents($file);
                $data = json_decode($json, true);

                if (!isset($data['rates']) || !is_array($data['rates'])) {
                    return 0.0;
                }

                self::$rates = $data['rates'];
            }

            $countryRates = self::$rates[$countryCode] ?? null;
            if (!$countryRates) {
                return 0.0;
            }

            // Fallback logic complet și legal
            return match ($type) {
                'standard'      => (float)($countryRates['standard_rate'] ?? 0.0),
                'reduced'       => is_numeric($countryRates['reduced_rate'] ?? null)
                    ? (float)$countryRates['reduced_rate']
                    : (float)($countryRates['standard_rate'] ?? 0.0),
                'reduced2'      => is_numeric($countryRates['reduced_rate_alt'] ?? null)
                    ? (float)$countryRates['reduced_rate_alt']
                    : (float)($countryRates['standard_rate'] ?? 0.0),
                'super_reduced' => is_numeric($countryRates['super_reduced_rate'] ?? null)
                    ? (float)$countryRates['super_reduced_rate']
                    : (float)($countryRates['standard_rate'] ?? 0.0),
                'zero'          => 0.0, // TVA zero e întotdeauna 0
                default         => (float)($countryRates['standard_rate'] ?? 0.0),
            };
        } catch (\Throwable $e) {
            return 0.0;
        }
    }

    /**
     * Verifică dacă țara are o rată validă pentru un tip de TVA.
     *
     * @param string $countryCode
     * @param string $type
     * @return bool
     */
    public static function hasRateFor(string $countryCode, string $type): bool
    {
        self::getRate($countryCode); // Încarcă ratele dacă nu sunt deja

        $mapping = [
            'standard' => 'standard_rate',
            'reduced' => 'reduced_rate',
            'reduced2' => 'reduced_rate_alt',
            'super_reduced' => 'super_reduced_rate',
            'zero' => 'zero_rate',
        ];

        $jsonKey = $mapping[$type] ?? 'standard_rate';
        $rate = self::$rates[$countryCode][$jsonKey] ?? null;

        return is_numeric($rate) && $rate > 0;
    }
}

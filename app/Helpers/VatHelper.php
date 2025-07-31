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
     * @param string $type Ex: 'standard_rate', 'reduced_rate', 'reduced_rate_alt', 'super_reduced_rate'
     * @return float
     */
    public static function getRate(string $countryCode, string $type = 'standard_rate'): float
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

            $rate = $countryRates[$type] ?? null;

            if (is_numeric($rate)) {
                return (float)$rate;
            }

            return (float)($countryRates['standard_rate'] ?? 0.0);
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

        $rate = self::$rates[$countryCode][$type] ?? null;

        return is_numeric($rate) && $rate > 0;
    }
}

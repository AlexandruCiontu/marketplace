<?php

namespace App\Helpers;

class VatHelper
{
    /**
     * Local cache for VAT rates.
     */
    protected static array $rates = [];

    /**
     * Returns the VAT rate for a country and specified type.
     *
     * If the vendor's chosen type isn't available for that country,
     * it automatically falls back to the standard rate.
     *
     * @param string $countryCode e.g., 'RO', 'DE'
     * @param string $type e.g., 'standard_rate', 'reduced_rate', 'reduced_rate_alt', 'super_reduced_rate'
     * @return float
     */
    public static function getRate(string $countryCode, string $type = 'standard_rate'): float
    {
        try {
            // Load data only once
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
     * Checks if the country has a valid rate for a VAT type.
     *
     * @param string $countryCode
     * @param string $type
     * @return bool
     */
    public static function hasRateFor(string $countryCode, string $type): bool
    {
        self::getRate($countryCode); // Load rates if not already

        $rate = self::$rates[$countryCode][$type] ?? null;

        return is_numeric($rate) && $rate > 0;
    }
}

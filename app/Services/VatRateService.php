<?php

namespace App\Services;

use App\Models\Product;
use App\Support\CountryCode;

class VatRateService
{
    /** @var array<string,array> */
    protected array $rates = [];

    public function __construct()
    {
        // Ensure $this->rates is keyed by ISO2 country code and contains:
        // standard_rate, reduced_rate, reduced_rate_alt, super_reduced_rate (false if not available)
        // Load once from your cached JSON.
        $json = base_path('storage/app/vat/rates.json');
        if (is_file($json)) {
            $data = json_decode(file_get_contents($json), true);
            $this->rates = isset($data['rates']) ? $data['rates'] : $data; // support both shapes
        }
    }

    /**
     * Normalize VAT type string.
     */
    protected function normalizeType(string $type): string
    {
        $type = strtolower(trim($type));
        $type = str_replace([' ', '-'], '_', $type);
        $allowed = ['standard','reduced','reduced_alt','super_reduced','zero'];
        return in_array($type, $allowed, true) ? $type : 'standard';
    }

    /**
     * Resolve VAT rate for a Product or raw type and country.
     */
    public function rateForProduct(mixed $productOrType, string $country): float
    {
        $type = $productOrType instanceof Product
            ? $productOrType->vat_type_normalized
            : $this->normalizeType((string) $productOrType);

        return $this->rateForType($type, $country);
    }

    /**
     * Resolve VAT rate for a given type and country code.
     */
    public function rateForType(string $type, string $country): float
    {
        $iso2 = strtoupper(CountryCode::toIso2($country) ?? 'RO');
        $type = $this->normalizeType($type);

        // 1) configured
        $configured = config("vat.rates.$type.$iso2");
        if ($configured !== null) {
            return (float) $configured;
        }

        // 2) fallback JSON (if available)
        $fieldMap = [
            'standard' => 'standard_rate',
            'reduced' => 'reduced_rate',
            'reduced_alt' => 'reduced_rate_alt',
            'super_reduced' => 'super_reduced_rate',
        ];
        $field = $fieldMap[$type] ?? null;
        if ($field) {
            $row = $this->rates[$iso2] ?? null;
            if ($row && array_key_exists($field, $row) && $row[$field] !== false) {
                return (float) $row[$field];
            }
            if ($type === 'reduced_alt') {
                $fallbackRow = $this->rates[$iso2]['reduced_rate'] ?? null;
                if ($fallbackRow !== null) {
                    return (float) $fallbackRow;
                }
            }
        }

        // 3) default
        return (float) config("vat.default_rates.$type", 21.0);
    }

    /**
     * Helper used by resources/controllers.
     */
    public function calculate(float $net, mixed $productOrType, ?string $country = null): array
    {
        $country = $country ?? session('country_code', config('vat.fallback_country', 'RO'));
        $country = strtoupper(CountryCode::toIso2($country) ?? $country);

        $rate = $this->rateForProduct($productOrType, $country);
        $vat  = round($net * $rate / 100, 2);
        $gross = round($net + $vat, 2);

        return [
            'price_net'   => (float) $net,
            'vat_rate'    => (float) $rate,
            'vat_amount'  => (float) $vat,
            'price_gross' => (float) $gross,
        ];
    }
}


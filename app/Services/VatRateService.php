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
     * Normalize a "product or type" input to a VAT type string.
     * Accepts: Product | array | stdClass | string
     */
    private function normalizeVatType(mixed $source): string
    {
        if (is_string($source)) {
            return strtolower(trim($source)) ?: 'standard';
        }
        if ($source instanceof Product) {
            return strtolower(trim((string) $source->vat_type ?: 'standard'));
        }
        if (is_array($source)) {
            return strtolower(trim((string) ($source['vat_type'] ?? 'standard')));
        }
        if (is_object($source)) {
            return strtolower(trim((string) ($source->vat_type ?? 'standard')));
        }
        return 'standard';
    }

    /**
     * Return the VAT percent for a product (or raw vat_type) and country.
     * Accepts Product | array | stdClass | string $productOrType
     */
    public function rateForProduct(mixed $productOrType, string $country): float
    {
        $iso2 = strtoupper(CountryCode::toIso2($country) ?? $country);
        $type = $this->normalizeVatType($productOrType); // standard | reduced | reduced_alt | super_reduced | zero

        $cfg = config('vat.rates', []);
        $pick = function (string $bucket) use ($cfg, $iso2): ?float {
            return isset($cfg[$bucket][$iso2]) ? (float) $cfg[$bucket][$iso2] : null;
        };

        $fromJson = function (string $field) use ($iso2): ?float {
            $row = $this->rates[$iso2] ?? null;
            if (!$row || !array_key_exists($field, $row)) return null;
            if ($row[$field] === false) return null;
            return (float) $row[$field];
        };

        $rate = null;
        switch ($type) {
            case 'zero':
                $rate = 0.0;
                break;

            case 'reduced':
                $rate = $pick('reduced') ?? $fromJson('reduced_rate');
                break;

            case 'reduced_alt':
                // RO 5% is usually in reduced_rate_alt
                $rate = $pick('reduced_alt') ?? $fromJson('reduced_rate_alt');
                if ($rate === null) $rate = $pick('reduced') ?? $fromJson('reduced_rate');
                break;

            case 'super_reduced':
                $rate = $pick('super_reduced') ?? $fromJson('super_reduced_rate');
                if ($rate === null) $rate = $pick('reduced_alt') ?? $fromJson('reduced_rate_alt');
                if ($rate === null) $rate = $pick('reduced') ?? $fromJson('reduced_rate');
                break;

            case 'standard':
            default:
                $rate = $pick('standard') ?? $fromJson('standard_rate');
                break;
        }

        if ($rate === null) {
            $defaults = config('vat.default_rates', []);
            $map = [
                'standard'       => 'standard',
                'reduced'        => 'reduced',
                'reduced_alt'    => 'reduced_alt',
                'super_reduced'  => 'super_reduced',
                'zero'           => 'zero',
            ];
            $key = $map[$type] ?? 'standard';
            $rate = (float) ($defaults[$key] ?? 21.0);
        }

        return (float) round($rate, 2);
    }

    /**
     * Helper used by resources/controllers.
     */
    public function calculate(float $net, mixed $productOrType, string $country): array
    {
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


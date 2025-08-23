<?php

namespace App\Services;

use App\Support\CountryCode;

class VatRateService
{
    /** @var array<string, array<string, mixed>> */
    protected array $rates = [];

    public function __construct()
    {
        $path = storage_path('app/vat/rates.json');
        if (is_file($path)) {
            $raw = json_decode(file_get_contents($path), true);
            $rows = $raw['rates'] ?? [];
            foreach ($rows as $code => $row) {
                $iso2 = strtoupper(CountryCode::toIso2($code, $code));
                $this->rates[$iso2] = $row;
            }
        }
    }

    /**
     * Return the VAT percent for a product and country.
     */
    public function rateForProduct(\App\Models\Product $product, string $country): float
    {
        $iso2 = strtoupper(CountryCode::toIso2($country) ?? $country);
        $type = strtolower(trim($product->vat_type ?? 'standard'));

        $cfg = config('vat.rates');
        $pick = function (string $bucket) use ($cfg, $iso2): ?float {
            return isset($cfg[$bucket][$iso2]) ? (float) $cfg[$bucket][$iso2] : null;
        };

        $fromJson = function (string $field) use ($iso2): ?float {
            $row = $this->rates[$iso2] ?? null;
            if (!$row || !array_key_exists($field, $row)) {
                return null;
            }
            if ($row[$field] === false) {
                return null;
            }
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
                $rate = $pick('reduced_alt') ?? $fromJson('reduced_rate_alt');
                if ($rate === null) {
                    $rate = $pick('reduced') ?? $fromJson('reduced_rate');
                }
                break;
            case 'super_reduced':
                $rate = $pick('super_reduced') ?? $fromJson('super_reduced_rate');
                if ($rate === null) {
                    $rate = $pick('reduced_alt') ?? $fromJson('reduced_rate_alt');
                }
                if ($rate === null) {
                    $rate = $pick('reduced') ?? $fromJson('reduced_rate');
                }
                break;
            case 'standard':
            default:
                $rate = $pick('standard') ?? $fromJson('standard_rate');
                break;
        }

        if ($rate === null) {
            $defaults = config('vat.default_rates');
            $map = [
                'standard' => 'standard',
                'reduced' => 'reduced',
                'reduced_alt' => 'reduced',
                'super_reduced' => 'super_reduced',
                'zero' => 'zero',
            ];
            $rate = (float) ($defaults[$map[$type] ?? 'standard'] ?? 21.0);
        }

        return (float) round($rate, 2);
    }

    /**
     * Calculează TVA-ul și totalul brut pentru un preț net.
     *
     * @return array{vat: float, gross: float, rate: float, country: string}
     */
    public function calculate(float $netAmount, string $rateType = 'standard_rate', ?string $countryCode = null): array
    {
        $code = $countryCode ?? session('country_code', config('vat.fallback_country', 'RO'));
        $code = strtoupper(CountryCode::toIso2($code) ?? config('vat.fallback_country', 'RO'));

        $type = strtolower($rateType);
        $rate = $this->rateForProduct((object) ['vat_type' => $type], $code);
        $vat = round($netAmount * ($rate / 100), 2);
        $gross = round($netAmount + $vat, 2);

        return [
            'vat' => $vat,
            'gross' => $gross,
            'rate' => $rate,
            'country' => $code,
        ];
    }
}


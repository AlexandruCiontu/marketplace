<?php

namespace App\Services;

use App\Support\CountryCode;
class VatRateService
{
    /**
     * Return the VAT percent for a product and country.
     */
    public function rateForProduct($product, string $country): float
    {
        // 1) Normalize country to ISO-2 uppercase
        $cc = CountryCode::toIso2($country) ?? config('vat.fallback_country', 'RO');
        $cc = strtoupper($cc);

        // 2) Normalize VAT type
        $type = strtolower((string) ($product->vat_type ?? $product->vat_rate_type ?? 'standard'));
        $type = in_array($type, ['reduced', 'super_reduced', 'zero']) ? $type : 'standard';

        // 3) Prefer config map
        $cfg = config("vat.rates.$type", []);
        if (is_array($cfg) && array_key_exists($cc, $cfg)) {
            return (float) $cfg[$cc];
        }

        // 4) Fallback: bundled JSON
        $json = $this->ratesFromJson();
        if (isset($json[$cc])) {
            $key = match ($type) {
                'reduced' => 'reduced_rate',
                'super_reduced' => 'super_reduced_rate',
                'zero' => 'parking_rate',
                default => 'standard_rate',
            };
            $v = $json[$cc][$key] ?? null;
            if (is_numeric($v)) {
                return (float) $v;
            }
        }

        // 5) Last resort: default_rates
        return (float) config("vat.default_rates.$type", 21.0);
    }

    protected function ratesFromJson(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $path = storage_path('app/vat/rates.json');
        if (is_file($path)) {
            $raw = json_decode(file_get_contents($path), true);
            $cache = $raw['rates'] ?? [];
        } else {
            $cache = [];
        }

        return $cache;
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
        $vat   = round($netAmount * ($rate / 100), 2);
        $gross = round($netAmount + $vat, 2);

        return [
            'vat'     => $vat,
            'gross'   => $gross,
            'rate'    => $rate,
            'country' => $code,
        ];
    }
}

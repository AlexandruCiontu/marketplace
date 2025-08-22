<?php

namespace App\Services;

use App\Support\CountryCode;
use Ibericode\Vat\Rates;

class VatRateService
{
    protected Rates $rates;

    public function __construct()
    {
        // Poți schimba path-ul dacă preferi altă locație pentru cache
        $this->rates = new Rates(storage_path('framework/cache/vat_rates.cache'));
    }

    /**
     * Returnează procentul TVA pentru o țară și un tip de rată.
     */
    public function getRate(string $countryCode, string $rateType): float
    {
        $rateKeyMap = [
            'standard_rate' => ['standard'],
            'reduced_rate' => ['reduced', 'reduced2'],
            'reduced_rate_alt' => ['reduced1', 'reduced2', 'reduced'],
            'super_reduced_rate' => ['super_reduced'],
        ];

        $rateKeys = $rateKeyMap[$rateType] ?? [$rateType];
        $rate = 0.0;

        foreach ($rateKeys as $key) {
            try {
                $rate = $this->rates->getRateForCountry($countryCode, $key);
            } catch (\Throwable $e) {
                $rate = 0.0;
            }

            if (is_numeric($rate) && $rate > 0) {
                break;
            }
        }

        if (! is_numeric($rate) || $rate <= 0) {
            try {
                $rate = $this->rates->getRateForCountry($countryCode, 'standard');
            } catch (\Throwable $e) {
                $rate = 0.0;
            }
        }

        return (float) $rate;
    }

    /**
     * Calculează TVA-ul și totalul brut pentru un preț net.
     *
     * @return array{vat: float, gross: float, rate: float, country: string}
     */
    public function calculate(float $netAmount, string $rateType = 'standard_rate', ?string $countryCode = null): array
    {
        $code = $countryCode ?? session('vat_country_code') ?? session('country_code') ?? 'RO';
        $code = CountryCode::toIso2($code);

        $rate  = $this->getRate($code, $rateType);
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

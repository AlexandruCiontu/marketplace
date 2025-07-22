<?php

namespace App\Services;

use Ibericode\Vat\Rates;

class VatService
{
    private Rates $rates;

    public function __construct()
    {
        // Make sure this path exists and is writable
        $this->rates = new Rates(storage_path('framework/cache/vat_rates.cache'));
    }

    /**
     * Calculate VAT and gross totals.
     *
     * @param  float       $netAmount   The net amount (e.g. price before VAT)
     * @param  string      $rateType    One of your VatRateTypeEnum values
     * @param  string|null $countryCode Two-letter ISO country code, optional
     * @return array<string, float>     ['rate' => float, 'vat' => float, 'gross' => float]
     */
    public function calculate(float $netAmount, string $rateType, ?string $countryCode = null): array
    {
        // if not passed in, try session
        $countryCode = $countryCode ?: session('country_code');

        if (! $countryCode) {
            return [
                'rate'  => 0.0,
                'vat'   => 0.0,
                'gross' => $netAmount,
            ];
        }

        // fetch the rate (percentage) for this country + rate type (e.g. 'standard')
        try {
            $rate = $this->rates->getRateForCountry($countryCode, $rateType) ?? 0.0;
        } catch (\Throwable $e) {
            $rate = 0.0;
        }

        // compute VAT amount and gross total
        $vat   = round($netAmount * $rate / 100, 2);
        $gross = round($netAmount + $vat, 2);

        return [
            'rate'  => $rate,
            'vat'   => $vat,
            'gross' => $gross,
        ];
    }
}

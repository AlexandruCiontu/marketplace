<?php

namespace App\Services;

use Ibericode\Vat\Rates;

class VatService
{
    private Rates $rates;

    public function __construct()
    {
        $this->rates = new Rates(storage_path('framework/cache/vat_rates.cache'));
    }

    public function calculate(float $netAmount, string $rateType, ?string $countryCode = null): array
    {
        $countryCode = $countryCode ?: session('country_code');
        if (! $countryCode) {
            return ['rate' => 0, 'vat' => 0, 'gross' => $netAmount];
        }

        try {
            $rate = $this->rates->getRateForCountry($countryCode, $rateType);
        } catch (\Throwable $e) {
            $rate = 0.0;
        }

        $vat = round($netAmount * $rate / 100, 2);
        $gross = round($netAmount + $vat, 2);

        return [
            'rate' => $rate,
            'vat' => $vat,
            'gross' => $gross,
        ];
    }
}

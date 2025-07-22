<?php

namespace App\Services;

use Dannyvankooten\LaravelVat\Facades\Rates;

class VatService
{
    public function calculate(float $netAmount, string $rateType, ?string $countryCode = null): array
    {
        $countryCode = $countryCode ?: session('country_code');
        if (! $countryCode) {
            return ['rate' => 0, 'vat' => 0, 'gross' => $netAmount];
        }

        $rate = Rates::country($countryCode, $rateType) ?? 0;
        $vat = round($netAmount * $rate / 100, 2);
        $gross = round($netAmount + $vat, 2);

        return [
            'rate' => $rate,
            'vat' => $vat,
            'gross' => $gross,
        ];
    }
}

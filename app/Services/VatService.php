<?php

namespace App\Services;

use Ibericode\Vat\Rates;

class VatService
{
    protected Rates $rates;

    public function __construct()
    {
        // Poți schimba path-ul dacă preferi altă locație pentru cache
        $this->rates = new Rates(storage_path('framework/cache/vat_rates.cache'));
    }

    /**
     * Calculează TVA-ul și totalul brut pentru un preț net.
     *
     * @param  float       $netAmount     Prețul fără TVA
     * @param  string      $rateType      Tipul TVA ('standard', 'reduced', etc.)
     * @param  string|null $countryCode   Codul țării (ex: 'RO', 'DE')
     * @return array<string, float>       ['rate' => TVA%, 'vat' => valoare TVA, 'gross' => total cu TVA]
     */
    public function calculate(float $netAmount, string $rateType, ?string $countryCode = null): array
    {
        $countryCode = $countryCode ?: session('country_code');

        if (! $countryCode) {
            return [
                'rate'  => 0.0,
                'vat'   => 0.0,
                'gross' => $netAmount,
            ];
        }

        try {
            $rate = $this->rates->getRateForCountry($countryCode, $rateType);

            // ✅ Fallback la 'standard' dacă rata e invalidă (false, null, 0 etc.)
            if (!is_numeric($rate) || $rate <= 0) {
                $rate = $this->rates->getRateForCountry($countryCode, 'standard_rate') ?? 0.0;
            }
        } catch (\Throwable $e) {
            $rate = 0.0;
        }

        $vat   = round($netAmount * $rate / 100, 2);
        $gross = round($netAmount + $vat, 2);

        return [
            'rate'  => $rate,
            'vat'   => $vat,
            'gross' => $gross,
        ];
    }
}

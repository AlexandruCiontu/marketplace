<?php

namespace Ibericode\Vat;

class Rates
{
    public function __construct($cachePath = null)
    {
        // no cache needed for stub
    }

    public function getRateForCountry(string $countryCode, string $key)
    {
        $rates = [
            'HU' => ['standard' => 27],
            'DE' => ['standard' => 19],
            'RO' => ['standard' => 19],
        ];

        return $rates[$countryCode][$key] ?? 0;
    }
}

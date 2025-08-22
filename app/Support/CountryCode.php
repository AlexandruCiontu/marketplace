<?php

namespace App\Support;

final class CountryCode
{
    /** Convert any "country-like" code to ISO-2 uppercase (ROU->RO, esp->ES, ro->RO). */
    public static function toIso2(?string $code, string $default = 'RO'): string
    {
        if (!$code) return $default;

        $code = strtoupper(trim($code));

        // Map a few common ISO3 -> ISO2 (extend as needed)
        $iso3 = [
            'ROU' => 'RO', 'ESP' => 'ES', 'DEU' => 'DE', 'HUN' => 'HU',
            'ITA' => 'IT', 'FRA' => 'FR', 'BGR' => 'BG', 'NLD' => 'NL',
        ];

        if (isset($iso3[$code])) return $iso3[$code];

        // If already 2 chars, assume ISO2
        if (strlen($code) === 2) return $code;

        return $default;
    }
}

<?php

namespace App\Support;

class CountryCodes
{
    /**
     * Convert ISO-3 alpha country code to ISO-2.
     * Falls back to the first two characters if the code is unknown.
     */
    public static function alpha3To2(string $code): string
    {
        $map = [
            'ROU' => 'RO', 'ESP' => 'ES', 'DEU' => 'DE', 'BGR' => 'BG',
            'FRA' => 'FR', 'ITA' => 'IT', 'NLD' => 'NL', 'BEL' => 'BE',
            'PRT' => 'PT', 'GRC' => 'GR', 'HUN' => 'HU', 'CZE' => 'CZ',
            'SVK' => 'SK', 'SVN' => 'SI', 'AUT' => 'AT', 'POL' => 'PL',
            'IRL' => 'IE', 'LVA' => 'LV', 'LTU' => 'LT', 'EST' => 'EE',
            'CYP' => 'CY', 'MLT' => 'MT', 'HRV' => 'HR', 'DNK' => 'DK',
            'FIN' => 'FI', 'SWE' => 'SE', 'LUX' => 'LU',
        ];

        $code = strtoupper(trim($code));
        return $map[$code] ?? substr($code, 0, 2);
    }
}

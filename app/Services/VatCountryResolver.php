<?php

namespace App\Services;

use App\Support\CountryCode;
use Illuminate\Http\Request;

class VatCountryResolver
{
    public function resolve(?Request $request = null): string
    {
        $request = $request ?? request();

        $country = $request->input('shipping.country_code')
            ?? $request->user()?->defaultAddress()?->country_code;

        $country ??= session('country_code', config('vat.fallback_country', 'RO'));

        return CountryCode::toIso2($country) ?? config('vat.fallback_country', 'RO');
    }
}

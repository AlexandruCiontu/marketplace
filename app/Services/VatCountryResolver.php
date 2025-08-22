<?php

namespace App\Services;

use App\Support\CountryCode;
use Illuminate\Http\Request;

class VatCountryResolver
{
    public function resolve(Request $request): string
    {
        // 1) Selected shipping address (request payload or persisted cart address)
        $country = $request->input('shipping.country_code')
            ?? $request->user()?->defaultAddress()?->country_code;

        // 2) Session country (geoIP etc.)
        $country ??= session('vat_country_code') ?? session('country_code', 'RO');

        return CountryCode::toIso2($country);
    }
}

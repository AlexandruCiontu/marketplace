<?php

namespace App\Services;

use App\Support\CountryCode;
use Illuminate\Http\Request;

class VatCountryResolver
{
    public function resolve(Request $request): string
    {
        // 1) Selected shipping address (request payload or persisted cart address)
        $addressCountry = $request->input('shipping.country_code')
            ?? optional(
                $request->user()?->addresses()->where('is_default', true)->first()
            )->country_code;

        if ($addressCountry) {
            return CountryCode::toIso2($addressCountry);
        }

        // 2) Session country (geoIP etc.)
        $sessionCountry = session('country_code');
        if ($sessionCountry) {
            return CountryCode::toIso2($sessionCountry);
        }

        // 3) Fallback
        return 'RO';
    }
}

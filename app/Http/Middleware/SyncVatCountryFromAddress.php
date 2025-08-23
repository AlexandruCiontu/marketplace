<?php

namespace App\Http\Middleware;

use App\Support\CountryCode;
use Closure;
use Illuminate\Http\Request;

class SyncVatCountryFromAddress
{
    public function handle(Request $request, Closure $next)
    {
        if (!config('vat.lock_to_default_address')) {
            return $next($request);
        }

        $raw  = $request->user()?->vatCountryCode();
        $iso2 = CountryCode::toIso2($raw);

        if ($iso2 && $iso2 !== session('country_code')) {
            session(['country_code' => strtoupper($iso2)]);
        }

        if (!$iso2 && !session()->has('country_code')) {
            session(['country_code' => config('vat.fallback_country', 'RO')]);
        }

        return $next($request);
    }
}

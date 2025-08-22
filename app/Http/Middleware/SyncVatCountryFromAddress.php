<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SyncVatCountryFromAddress
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Use default shipping address country if available
        $country = strtoupper(optional($user?->defaultShippingAddress)->country_code ?? '');

        if ($country && $country !== session('country_code')) {
            session(['country_code' => $country]);
        }

        // fallback to configured default if no country present
        if (!$country && !session()->has('country_code')) {
            session(['country_code' => config('vat.fallback_country', 'RO')]);
        }

        return $next($request);
    }
}

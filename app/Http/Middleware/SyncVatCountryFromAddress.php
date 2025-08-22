<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SyncVatCountryFromAddress
{
    public function handle(Request $request, Closure $next)
    {
        if (!config('vat.lock_to_default_address')) {
            return $next($request);
        }

        $user = $request->user();
        $country = strtoupper($user?->vatCountryCode() ?? '');

        if ($country && $country !== session('country_code')) {
            session(['country_code' => $country]);
        }

        if (!$country && !session()->has('country_code')) {
            session(['country_code' => config('vat.fallback_country', 'RO')]);
        }

        return $next($request);
    }
}

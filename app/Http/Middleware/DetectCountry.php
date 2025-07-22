<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stevebauman\Location\Facades\Location;

class DetectCountry
{
    public function handle(Request $request, Closure $next)
    {
        if (! session()->has('country_code')) {
            try {
                $position = Location::get($request->ip());
                $code = $position?->countryCode ?: 'RO';
            } catch (\Exception $e) {
                $code = 'RO';
            }

            session(['country_code' => $code]);
        }

        return $next($request);
    }
}

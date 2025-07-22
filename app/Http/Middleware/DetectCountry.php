<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use GeoIp2\Database\Reader;

class DetectCountry
{
    public function handle(Request $request, Closure $next)
    {
        if (! session()->has('country_code')) {
            try {
                $reader = new Reader(storage_path('app/GeoLite2-Country.mmdb'));
                $record = $reader->country($request->ip());
                $code = $record->country->isoCode ?: 'RO';
            } catch (\Exception $e) {
                $code = 'RO';
            }

            session(['country_code' => $code]);
        }

        return $next($request);
    }
}

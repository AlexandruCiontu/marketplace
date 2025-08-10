<?php

namespace App\Http\Middleware;

use Closure;
use GeoIp2\Database\Reader;
use Illuminate\Http\Request;

class DetectCountryFromGeoIP
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $reader = new Reader(storage_path('app/GeoLite2-Country.mmdb'));
            $ip = $request->ip();
            $record = $reader->country($ip);
            $countryCode = $record->country->isoCode ?? 'RO'; // fallback to RO if it fails

            session(['country_code' => $countryCode]);
        } catch (\Exception $e) {
            session(['country_code' => 'RO']);
        }

        return $next($request);
    }
}

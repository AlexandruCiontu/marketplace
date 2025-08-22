<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\CountryCode;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function select(Request $request)
    {
        $code = CountryCode::toIso2($request->input('country_code'));
        session(['country_code' => $code]);

        return [
            'country_code' => $code,
        ];
    }

    public function current(Request $request)
    {
        $code = session('country_code', config('vat.fallback_country', 'RO'));

        return [
            'country_code' => $code,
        ];
    }
}


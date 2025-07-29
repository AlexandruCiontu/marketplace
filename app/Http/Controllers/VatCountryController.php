<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VatCountryController extends Controller
{
    public function show()
    {
        $code = session('vat_country', session('country_code', config('app.vat_fallback_country', 'RO')));
        return response()->json(['country_code' => $code]);
    }

    public function update(Request $request)
    {
        $code = strtoupper($request->get('country_code', 'RO'));
        session(['vat_country' => $code, 'country_code' => $code]);
        return response()->json(['country_code' => $code]);
    }
}

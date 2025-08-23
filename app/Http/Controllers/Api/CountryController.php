<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    public function select(Request $request)
    {
        if (config('vat.lock_to_default_address')) {
            return response()->json([
                'message' => 'VAT country is locked to default shipping address. Change your default address to update VAT.',
            ], 409);
        }

        $data = $request->validate([
            'country_code' => ['required', 'string', 'size:2'],
        ]);

        session(['country_code' => strtoupper($data['country_code'])]);

        return ['country_code' => strtoupper(session('country_code'))];
    }

    public function current()
    {
        return [
            'country_code' => strtoupper(session('country_code', config('vat.fallback_country', 'RO'))),
        ];
    }
}


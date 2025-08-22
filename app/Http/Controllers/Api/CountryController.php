<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
class CountryController extends Controller
{
    public function current()
    {
        $code = session('country_code', config('vat.fallback_country', 'RO'));

        return [
            'country_code' => $code,
        ];
    }
}


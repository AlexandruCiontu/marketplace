<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VatRateService;
use Illuminate\Http\Request;

class VatRateController extends Controller
{
    public function __invoke(Request $request, VatRateService $vatRateService)
    {
        $country = $request->query('country_code');
        $type = $request->query('rate_type', 'standard_rate');
        $amount = (float) $request->query('amount', 0);

        $result = $vatRateService->calculate($amount, $type, $country);

        return response()->json([
            'rate' => $result['rate'],
        ]);
    }
}

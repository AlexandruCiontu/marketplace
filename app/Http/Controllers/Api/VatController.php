<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\VatRateService;
use Illuminate\Http\Request;

class VatController extends Controller
{
    public function priceBatch(Request $request, VatRateService $vat)
    {
        $ids = array_map('intval', (array) $request->query('ids', []));
        $country = session('country_code', config('vat.fallback_country', 'RO'));

        $products = Product::whereIn('id', $ids)->get(['id', 'price', 'vat_rate_type']);

        $items = $products->map(function ($p) use ($vat, $country) {
            $percent = $vat->getRate($country, $p->vat_rate_type ?? 'standard_rate');
            $unitVat = round($p->price * $percent / 100, 2);
            $unitGross = round($p->price + $unitVat, 2);
            return [
                'id' => (int) $p->id,
                'unit_gross' => (float) $unitGross,
                'vat_rate' => (float) $percent,
            ];
        })->values();

        return response()->json([
            'country_code' => $country,
            'items' => $items,
        ]);
    }
}


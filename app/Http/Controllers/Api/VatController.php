<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\VatCountryResolver;
use App\Services\VatRateService;
use Illuminate\Http\Request;

class VatController extends Controller
{
    /**
     * Return pricing breakdown for multiple products.
     * Response: { prices: {id: {price_net, vat_rate, vat_amount, price_gross}} }
     */
    public function priceBatch(
        Request $request,
        VatCountryResolver $country,
        VatRateService $vat
    ) {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(array_map('trim', explode(',', $ids)));
        }
        $ids = array_values(array_unique(array_map('intval', (array) $ids)));
        if (empty($ids)) {
            return response()->json(['prices' => []]);
        }

        $countryCode = $country->resolve($request);

        $products = Product::query()
            ->whereIn('id', $ids)
            ->where('status', 'published')
            ->get(['id', 'price', 'vat_type']);

        $prices = [];
        foreach ($products as $p) {
            $prices[(string) $p->id] = $vat->calculate((float) $p->price, $p, $countryCode);
        }

        return response()->json(['prices' => $prices]);
    }
}


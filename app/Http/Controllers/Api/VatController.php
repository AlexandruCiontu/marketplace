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
    public function priceBatch(Request $request, VatRateService $vat, VatCountryResolver $countryResolver)
    {
        $raw = $request->query('ids', []);
        if (is_string($raw)) {
            $raw = preg_split('/[,\s]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        }
        $ids = collect($raw)->map(fn($v) => (int) $v)->filter()->values();

        if ($ids->isEmpty()) {
            return response()->json([]);
        }

        $country = $countryResolver->resolve();

        $products = Product::query()
            ->whereIn('id', $ids)
            ->get(['id', 'price', 'vat_type']);

        $result = $products->mapWithKeys(function (Product $p) use ($vat, $country) {
            $calc = $vat->calculate($p->price, $p, $country);
            return [$p->id => $calc];
        });

        return response()->json($result);
    }
}


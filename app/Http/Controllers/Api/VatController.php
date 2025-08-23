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
     * Response: { id: { price_net, vat_rate, vat_amount, price_gross, unit_gross } }
     */
    public function priceBatch(Request $request, VatCountryResolver $countryResolver, VatRateService $vatService)
    {
        // Accept both ids[]=1&ids[]=2 and ids=1,2,3
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = preg_split('/[,\s;]+/', $ids, -1, PREG_SPLIT_NO_EMPTY);
        }
        if (!is_array($ids)) {
            $ids = [];
        }
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (empty($ids)) {
            return response()->json([]);
        }

        $country = $countryResolver->resolve($request);

        // IMPORTANT: do not select vat_type from DB
        $products = Product::query()
            ->whereIn('id', $ids)
            ->where('status', 'published')
            ->select(['id', 'price'])
            ->get();

        $out = [];
        foreach ($products as $product) {
            $rate = $vatService->rateForProduct($product, $country);
            $calc = $vatService->calculate($product->price, $rate);

            $out[$product->id] = [
                'price_net'   => $calc['price_net'],
                'vat_rate'    => $rate,
                'vat_amount'  => $calc['vat_amount'],
                'price_gross' => $calc['price_gross'],
                // compat old field
                'unit_gross'  => $calc['price_gross'],
            ];
        }

        return response()->json($out);
    }
}


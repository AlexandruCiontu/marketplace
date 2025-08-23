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

        $products = Product::query()
            ->whereIn('id', $ids)
            ->where('status', 'published')
            ->select(['id', 'price', 'vat_type'])
            ->get();

        $out = [];
        foreach ($products as $product) {
            $rate = $vatService->rateForProduct($product, $country);
            $net  = round((float)$product->price, 2);
            $vatA = round($net * $rate / 100, 2);
            $out[$product->id] = [
                'net'   => $net,
                'vat'   => $vatA,
                'rate'  => $rate,
                'gross' => $net + $vatA,
            ];
        }

        return response()->json($out);
    }
}


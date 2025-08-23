<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\VatCountryResolver;
use App\Services\VatRateService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class VatController extends Controller
{
    /**
     * Return breakdown for a batch of product ids.
     * Response: { data: [id => {price_net, vat_rate, vat_amount, price_gross}] }
     */
    public function priceBatch(
        Request $request,
        VatRateService $service,
        VatCountryResolver $resolver
    ) {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }
        $ids = array_values(array_unique(array_map('intval', Arr::wrap($ids))));
        if (empty($ids)) {
            return response()->json(['data' => []]);
        }

        $country = $resolver->resolve($request);

        $products = Product::query()
            ->select(['id', 'price', 'vat_type'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $out = [];
        foreach ($ids as $id) {
            $p = $products->get($id);
            if (!$p) {
                continue;
            }
            $calc = $service->calculate((float) $p->price, $p, $country);
            $out[$id] = [
                'price_net'   => $calc['price_net'],
                'vat_rate'    => $calc['vat_rate'],
                'vat_amount'  => $calc['vat_amount'],
                'price_gross' => $calc['price_gross'],
            ];
        }

        return response()->json(['data' => $out]);
    }
}


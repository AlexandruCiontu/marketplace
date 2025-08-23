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
     * Return breakdown for a batch of products keyed by product id.
     * Result: [id => {price_net, vat_rate, vat_amount, price_gross}]
     */
    public function priceBatch(Request $request, VatRateService $service, VatCountryResolver $resolver)
    {
        $ids = array_values(array_filter((array) $request->query('ids', []), fn ($v) => is_numeric($v)));
        if (empty($ids)) {
            return [];
        }

        $country = $resolver->resolve($request);

        $products = Product::query()
            ->whereIn('id', $ids)
            ->get(['id', 'price', 'vat_type']);

        $out = [];
        foreach ($products as $p) {
            $out[$p->id] = $service->calculate((float) $p->price, $p, $country);
        }

        return $out;
    }
}


<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\VatRateService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VatPriceController extends Controller
{
    public function batch(Request $request, VatRateService $svc)
    {
        $data = $request->validate([
            'ids'          => ['required','array','min:1'],
            'ids.*'        => ['string'],
            'country_code' => ['nullable','string','size:2'],
            'currency'     => ['nullable','string', Rule::in(['EUR','RON','HUF','BGN'])],
        ]);

        $ids = $data['ids'];
        $idsNumeric = collect($ids)->filter(fn($v) => ctype_digit((string)$v))->map(fn($v) => (int)$v)->all();
        $idsSlug    = collect($ids)->reject(fn($v) => ctype_digit((string)$v))->values()->all();

        $products = Product::query()
            ->when($idsNumeric, fn($q) => $q->orWhereIn('id', $idsNumeric))
            ->when($idsSlug, fn($q) => $q->orWhereIn('slug', $idsSlug))
            ->get(['id','slug','price','vat_type']);

        $country  = strtoupper($data['country_code'] ?? config('vat.default_country', 'RO'));
        $currency = strtoupper($data['currency'] ?? config('vat.default_currency', 'EUR'));

        $out = [];
        foreach ($ids as $lookup) {
            $product = $products->first(function ($p) use ($lookup) {
                return (string)$p->id === (string)$lookup || (string)$p->slug === (string)$lookup;
            });

            if (!$product) {
                $out[$lookup] = ['found' => false];
                continue;
            }

            $price = $svc->priceForProduct($product, $country, $currency);

            $out[$lookup] = array_merge([
                'found' => true,
                'id'    => $product->id,
                'slug'  => $product->slug,
            ], $price);
        }

        return response()->json([
            'country'  => $country,
            'currency' => $currency,
            'items'    => $out,
        ]);
    }
}


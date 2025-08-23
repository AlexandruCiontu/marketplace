<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Services\VatRateService;

class VatPriceController extends Controller
{
    public function show(Request $request)
    {
        try {
            $net = (int) $request->integer('price');
            $country = $request->string('country')->toString() ?: 'RO';
            $rate = $request->string('rate')->toString() ?: 'standard';

            $vatRate = 0.19;
            $vat = (int) round($net * $vatRate);
            $gross = $net + $vat;

            return response()->json([
                'net' => $net,
                'vat_amount' => $vat,
                'gross' => $gross,
                'country' => $country,
                'rate' => $rate,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'VAT_PRICE_CALC_FAILED',
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function batch(Request $request)
    {
        $data = $request->validate([
            'ids'          => ['required','array','min:1'],
            'ids.*'        => ['string'],
            'country_code' => ['nullable','string','size:2'],
            'currency'     => ['nullable','string', Rule::in(['EUR','RON','HUF','BGN'])],
        ]);

        $ids = collect($data['ids']);
        $idsNumeric = $ids->filter(fn ($v) => ctype_digit((string) $v))->map(fn ($v) => (int) $v)->all();
        $idsSlug    = $ids->reject(fn ($v) => ctype_digit((string) $v))->values()->all();

        $products = Product::query()
            ->when($idsNumeric, fn ($q) => $q->orWhereIn('id', $idsNumeric))
            ->when($idsSlug, fn ($q) => $q->orWhereIn('slug', $idsSlug))
            ->get(['id', 'slug', 'price', 'vat_type']);

        $country  = strtoupper($data['country_code'] ?? config('vat.default_country', 'RO'));
        $currency = strtoupper($data['currency']     ?? config('vat.default_currency', 'EUR'));

        /** @var VatRateService $svc */
        $svc = app(VatRateService::class);

        $out = [];
        foreach ($ids as $key) {
            $p = $products->first(fn ($pp) => (string) $pp->id === (string) $key || (string) $pp->slug === (string) $key);
            if (!$p) {
                $out[$key] = ['found' => false];
                continue;
            }

            $price = $svc->priceForProduct($p, $country, $currency);
            $out[$key] = array_merge(['found' => true, 'id' => $p->id, 'slug' => $p->slug], $price);
        }

        return response()->json([
            'country'  => $country,
            'currency' => $currency,
            'items'    => $out,
        ]);
    }
}


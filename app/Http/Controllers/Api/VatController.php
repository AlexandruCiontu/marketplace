<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\VatCountryResolver;
use App\Services\VatRateService;
use Illuminate\Http\Request;

class VatController extends Controller
{
    public function priceBatch(Request $request, VatRateService $vat, VatCountryResolver $countryResolver)
    {
        // ids poate fi fie array (ids[]=...), fie string "1,2,slug-3"
        $raw = $request->query('ids', []);
        if (is_string($raw)) {
            $raw = preg_split('/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        }
        $raw = array_values(array_filter(array_map('strval', (array) $raw)));

        $idInts = [];
        $slugs  = [];
        foreach ($raw as $v) {
            if (ctype_digit($v)) {
                $idInts[] = (int) $v;
            } else {
                $slugs[] = $v;
            }
        }

        $country = $countryResolver->resolve();

        $products = Product::query()
            ->when($idInts, fn($q) => $q->whereIn('id', $idInts))
            ->when($slugs, fn($q) => $q->orWhereIn('slug', $slugs))
            ->get(['id', 'slug', 'price', 'vat_type']);

        $out = [];
        foreach ($products as $p) {
            $calc = $vat->calculate($p->price, $p, $country); // [price_net, vat_rate, vat_amount, price_gross]
            // cheiem rezultatul pe ambele variante, ca frontul să poată mapa indiferent ce cheie are
            $out[(string) $p->id] = $calc;
            $out[$p->slug]       = $calc;
        }

        return response()->json($out);
    }
}


<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\VatCountryResolver;
use App\Services\VatRateService;
use Illuminate\Http\Request;

class VatController extends Controller
{
    public function priceBatch(Request $request, VatCountryResolver $resolver, VatRateService $vat)
    {
        $tokens = collect((array)$request->input('ids', []))
            ->map(fn ($v) => (string)$v)
            ->filter(fn ($v) => $v !== '')
            ->values();

        if ($tokens->isEmpty()) {
            return response()->json([]);
        }

        $idInts = [];
        $slugs = [];
        foreach ($tokens as $t) {
            if (ctype_digit($t)) {
                $idInts[] = (int)$t;
            } else {
                $slugs[] = $t;
            }
        }

        $products = Product::query()
            ->when($idInts, fn($q) => $q->whereIn('id', $idInts))
            ->when($slugs, fn($q) => $q->orWhereIn('slug', $slugs))
            ->get(['id','slug','price','vat_type']);

        $byId = $products->keyBy('id');
        $bySlug = $products->keyBy('slug');

        $country = $resolver->resolve($request);
        $out = [];

        foreach ($tokens as $t) {
            $p = $bySlug[$t] ?? (ctype_digit($t) ? ($byId[(int)$t] ?? null) : null);
            if (!$p) { continue; }
            $rate = $vat->rateForProduct($p, $country);
            $net = (float)$p->price;
            $vatAmount = round($net * ($rate/100), 2);
            $gross = round($net + $vatAmount, 2);
            $out[$t] = ['net'=>$net,'vat'=>$vatAmount,'gross'=>$gross,'rate'=>$rate];
        }

        return response()->json($out);
    }
}


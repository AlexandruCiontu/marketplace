<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\VatCountryResolver;
use App\Services\VatRateService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VatController extends Controller
{
    public function priceBatch(Request $request, VatCountryResolver $countryResolver, VatRateService $rates)
    {
        // 1) read ids[] from query (?ids[]=1&ids[]=reduced)
        $raw = $request->query('ids', []);
        if (!is_array($raw)) {
            $raw = array_filter(array_map('trim', explode(',', (string)$raw)));
        }

        $keys = collect($raw)
            ->map(fn($v) => trim((string)$v))
            ->filter()
            ->unique()
            ->values();

        if ($keys->isEmpty()) {
            return response()->json([]);
        }

        // 2) split numeric vs slug
        $idInts = $keys->filter(fn($k) => ctype_digit($k))->map(fn($k) => (int)$k)->values();
        $slugs = $keys->reject(fn($k) => ctype_digit($k))->values();

        // 3) fetch products via separate queries
        $byId = $idInts->isEmpty() ? collect() :
            Product::query()
                ->select('id','slug','price','vat_type','status')
                ->where('status','published')
                ->whereIn('id', $idInts)
                ->get();

        $bySlug = $slugs->isEmpty() ? collect() :
            Product::query()
                ->select('id','slug','price','vat_type','status')
                ->where('status','published')
                ->whereIn('slug', $slugs->all())
                ->get();

        $products = $byId->concat($bySlug);

        // 4) index by id and slug
        $index = [];
        foreach ($products as $p) {
            $index[(string)$p->id] = $p;
            $index[$p->slug] = $p;
        }

        // 5) resolve country
        $country = (string)($countryResolver->resolve($request) ?: 'RO');

        // 6) build response keeping exact keys
        $out = [];
        foreach ($keys as $k) {
            if (!isset($index[$k])) {
                continue;
            }
            $p = $index[$k];
            $rate = (float)$rates->rateForProduct($p, $country);
            $net = (float)$p->price;
            $vat = round($net * $rate / 100, 2);
            $gross = round($net + $vat, 2);
            $out[$k] = [
                'rate' => $rate,
                'net' => $net,
                'vat' => $vat,
                'gross' => $gross,
                'id' => (int)$p->id,
                'slug' => $p->slug,
            ];
        }

        return response()->json($out);
    }
}

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
     * Accepts ids[]=... where each value can be a numeric ID or a slug.
     * The response is keyed by the exact identifier provided in the query.
     */
    public function priceBatch(Request $request, VatCountryResolver $countryResolver, VatRateService $vatService)
    {
        $rawIds = (array) $request->query('ids', []);
        $rawIds = array_values(array_filter($rawIds, fn ($v) => $v !== null && $v !== ''));

        if (empty($rawIds)) {
            return response()->json([]);
        }

        $numericIds = [];
        $slugIds = [];
        foreach ($rawIds as $id) {
            if (ctype_digit((string) $id)) {
                $numericIds[] = (int) $id;
            } else {
                $slugIds[] = (string) $id;
            }
        }

        $products = Product::query()
            ->select(['id', 'slug', 'price', 'vat_type', 'status'])
            ->where('status', 'published')
            ->when($numericIds, fn ($q) => $q->whereIn('id', $numericIds))
            ->when($slugIds, fn ($q) => $q->orWhereIn('slug', $slugIds))
            ->get();

        $country = $countryResolver->resolve($request);
        $currency = config('app.currency', 'EUR');

        $incomingSet = array_flip(array_map('strval', $rawIds));
        $out = [];
        foreach ($products as $product) {
            $rate = (float) $vatService->rateForProduct($product, $country);
            $net = (float) $product->price;
            $vatA = round($net * $rate / 100, 2);
            $gross = round($net + $vatA, 2);

            $possibleKeys = [(string) $product->id, (string) $product->slug];
            $key = (string) $product->id;
            foreach ($possibleKeys as $k) {
                if (isset($incomingSet[(string) $k])) {
                    $key = (string) $k;
                    break;
                }
            }

            $out[$key] = [
                'net' => $net,
                'vat' => $vatA,
                'gross' => $gross,
                'rate' => $rate,
                'currency' => $currency,
            ];
        }

        return response()->json($out);
    }
}


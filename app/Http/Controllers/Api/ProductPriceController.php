<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\VatRateService;
use App\Support\CountryCode;
use Illuminate\Http\Request;

class ProductPriceController extends Controller
{
    public function __invoke(Request $request, Product $product, VatRateService $service)
    {
        $price = (float) $request->query('price', $product->price);

        $country = session('country_code', config('vat.fallback_country', 'RO'));
        $country = strtoupper(CountryCode::toIso2($country) ?? 'RO');

        $rate = $service->rateForProduct($product, $country);
        $vat = round($price * $rate / 100, 2);
        $gross = round($price + $vat, 2);

        return [
            'price_net' => $price,
            'vat_rate' => $rate,
            'vat_amount' => $vat,
            'price_gross' => $gross,
            'country_code' => $country,
        ];
    }
}


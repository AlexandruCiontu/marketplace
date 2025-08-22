<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\VatRateService;
use Illuminate\Http\Request;

class ProductPriceController extends Controller
{
    public function __invoke(Request $request, Product $product, VatRateService $service)
    {
        $price = (float) $request->query('price', $product->price);
        $calc = $service->calculate($price, $product->vat_rate_type);

        return [
            'price_net' => $price,
            'vat_rate' => $calc['rate'],
            'vat_amount' => $calc['vat'],
            'price_gross' => $calc['gross'],
            'country_code' => $calc['country'],
        ];
    }
}


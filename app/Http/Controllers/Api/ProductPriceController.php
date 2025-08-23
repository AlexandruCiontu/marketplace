<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductPriceController extends Controller
{
    public function show(Request $request, Product $product)
    {
        $net = (int) $request->integer('price');

        $vatRate = match ($product->vat_type ?? 'standard') {
            'reduced', 'reduced_alt' => 0.09,
            default => 0.19,
        };
        $vat = (int) round($net * $vatRate);
        $gross = $net + $vat;

        return response()->json([
            'price_gross' => $gross,
            'vat_amount'  => $vat,
            'vat_rate'    => $vatRate * 100,
        ]);
    }
}


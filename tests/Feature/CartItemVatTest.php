<?php

use App\Models\CartItem;
use App\Models\Product;
use App\Http\Resources\CartItemResource;
use Illuminate\Support\Facades\Session;

it('returns vat details based on session country', function () {
    $product = new Product([
        'id' => 1,
        'title' => 'Test',
        'slug' => 'test',
        'price' => 100,
        'vat_rate_type' => 'reduced2',
        'quantity' => 10,
    ]);

    $cartItem = new CartItem([
        'id' => 1,
        'product_id' => $product->id,
        'user_id' => 1,
        'quantity' => 1,
        'price' => 100,
        'variation_type_option_ids' => [],
    ]);
    $cartItem->setRelation('product', $product);

    session(['country_code' => 'BG']);

    $array = (new CartItemResource($cartItem))->toArray(request());

    expect($array['vat_rate'])->toBe(9.0)
        ->and($array['gross_price'])->toBe(109.0)
        ->and($array['vat_amount'])->toBe(9.0);
});

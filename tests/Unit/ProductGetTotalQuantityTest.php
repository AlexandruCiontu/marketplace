<?php

use App\Models\Product;
use App\Models\ProductVariation;
use Tests\TestCase;

uses(TestCase::class);

test('getTotalQuantity matches variations regardless of option order', function () {
    $product = new Product(['quantity' => 10]);
    $variation = new ProductVariation([
        'variation_type_option_ids' => [1, 2],
        'quantity' => 3,
    ]);

    $product->setRelation('variations', collect([$variation]));

    $quantity = $product->getTotalQuantity([2, 1]);

    expect($quantity)->toBe(3);
});

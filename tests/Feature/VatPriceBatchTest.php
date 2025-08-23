<?php

use App\Models\Product;

it('returns VAT prices for ids and slugs', function () {
    $p1 = Product::factory()->create(['price'=>100, 'vat_type'=>'standard', 'slug'=>'standard']);
    $p2 = Product::factory()->create(['price'=>100, 'vat_type'=>'reduced', 'slug'=>'reduced']);

    $res = $this->postJson('/api/vat/price-batch', [
        'ids' => ['standard', (string)$p2->id],
        'country_code' => 'RO',
    ])->assertOk()->json('items');

    expect($res['standard']['gross'])->toBeFloat();
    expect($res[(string)$p2->id]['found'])->toBeTrue();
});


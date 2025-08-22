<?php

use App\Models\Product;

uses(Tests\TestCase::class);

test('scopeForVendor does not alter the query when no user is authenticated', function () {
    $baseSql = Product::query()->toSql();

    $scopedSql = Product::query()->forVendor()->toSql();

    expect($scopedSql)->toBe($baseSql);
});


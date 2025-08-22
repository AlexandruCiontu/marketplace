<?php

use App\Models\Product;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

it('returns product images when options have none', function () {
    $productImages = collect([Mockery::mock(Media::class)]);
    $product = Mockery::mock(Product::class)->makePartial();
    $product->shouldReceive('getMedia')->with('images')->andReturn($productImages);

    $option = Mockery::mock();
    $option->shouldReceive('getMedia')->with('images')->andReturn(collect());

    $product->setRelation('options', collect([$option]));

    $result = $product->getImages();

    expect($result)->toBe($productImages);
});

it('returns product images for getImagesForOptions when options lack images', function () {
    $productImages = collect([Mockery::mock(Media::class)]);
    $product = Mockery::mock(Product::class)->makePartial();
    $product->shouldReceive('getMedia')->with('images')->andReturn($productImages);

    $option = Mockery::mock();
    $option->shouldReceive('getMedia')->with('images')->andReturn(collect());

    $alias = Mockery::mock('alias:App\\Models\\VariationTypeOption');
    $alias->shouldReceive('whereIn')->with('id', [1])->andReturnSelf();
    $alias->shouldReceive('get')->andReturn(collect([$option]));

    $result = $product->getImagesForOptions([1]);

    expect($result)->toBe($productImages);
});

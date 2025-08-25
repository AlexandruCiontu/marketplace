<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Session\Middleware\StartSession;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\VatPriceController;
use App\Http\Controllers\Api\ProductPriceController;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;

Route::middleware(['web', StartSession::class])->group(function () {
    Route::post('country/select', [CountryController::class, 'select']);
    Route::get('country/current', [CountryController::class, 'current'])->name('api.country.current');
    Route::get('products/{product}/price', [ProductPriceController::class, 'show']);
    Route::get('/products/{product}', function (Request $request, Product $product) {
        return new ProductResource($product);
    });
});

Route::post('/vat/price-batch', [VatPriceController::class, 'batch'])
    ->name('api.vat.price-batch');


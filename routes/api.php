<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Session\Middleware\StartSession;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\VatController;
use App\Http\Controllers\Api\ProductPriceController;

Route::middleware(['web', StartSession::class])->group(function () {
    Route::post('country/select', [CountryController::class, 'select']);
    Route::get('country/current', [CountryController::class, 'current'])->name('api.country.current');
    Route::get('products/{product}/price', [ProductPriceController::class, 'show']);
});

Route::prefix('vat')->group(function () {
    Route::get('price-batch', [VatController::class, 'priceBatch'])
        ->name('api.vat.price-batch');
});


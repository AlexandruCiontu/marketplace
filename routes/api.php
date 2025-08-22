<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\ProductPriceController;

Route::middleware('web')->group(function () {
    Route::post('/country/select', [CountryController::class, 'select']);
    Route::get('/country/current', [CountryController::class, 'current']);
    Route::get('/products/{product}/price', ProductPriceController::class);
});

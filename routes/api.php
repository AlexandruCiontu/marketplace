<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VatRateController;

Route::get('/vat-rate', VatRateController::class);

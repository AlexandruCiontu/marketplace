<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\ShippingAddressController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\ProductPriceController;
use App\Http\Controllers\Api\VatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Stevebauman\Location\Facades\Location;

Route::get('/test-location', function () {
    $position = Location::get(request()->ip());

    if (! $position) {
        return response()->json([
            'error' => 'Nu s-a putut determina locația pentru IP: ' . request()->ip(),
        ]);
    }

    return response()->json([
        'ip' => request()->ip(),
        'countryCode' => $position->countryCode,
        'countryName' => $position->countryName,
        'regionName' => $position->regionName,
        'cityName' => $position->cityName,
    ]);
});

Route::get('/api/country/current', [CountryController::class, 'current'])->name('api.country.current');
Route::get('/api/products/{product}/price', ProductPriceController::class);
Route::get('/api/vat/price-batch', [VatController::class, 'priceBatch']);

// Guest Routes
Route::get('/', [ProductController::class, 'home'])->name('dashboard');
Route::get('/product/{product:slug}', [ProductController::class, 'show'])->name('product.show');
Route::get('/d/{department:slug}', [ProductController::class, 'byDepartment'])->name('product.byDepartment');
Route::get('/s/{vendor:store_name}', [VendorController::class, 'profile'])->name('vendor.profile');

Route::controller(CartController::class)->group(function () {
    Route::get('/cart', 'index')->name('cart.index');
    Route::post('/cart/add/{product}', 'store')->name('cart.store');
    Route::put('/cart/{product}', 'update')->name('cart.update');
    Route::delete('/cart/{product}', 'destroy')->name('cart.destroy');
    Route::put('/cart/update-shipping-address/{address}', 'updateShippingAddress')->name('cart.shippingAddress');
});

Route::post('/stripe/webhook', [StripeController::class, 'webhook'])->name('stripe.webhook');

// Auth routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/shipping-address', [ShippingAddressController::class, 'index'])->name('shippingAddress.index');
    Route::post('/shipping-address', [ShippingAddressController::class, 'store'])->name('shippingAddress.store');
    Route::put('/shipping-address/{address}', [ShippingAddressController::class, 'update'])->name('shippingAddress.update');
    Route::put('/shipping-address/make-default/{address}', [ShippingAddressController::class, 'makeDefault'])->name('shippingAddress.makeDefault');
    Route::delete('/shipping-address/{address}', [ShippingAddressController::class, 'destroy'])->name('shippingAddress.destroy');

    // ✅ VAT Country selector (manual override)

    // Orders routes for buyers
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');

    Route::middleware(['verified'])->group(function () {
        Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
        Route::get('/stripe/success', [StripeController::class, 'success'])->name('stripe.success');
        Route::get('/stripe/failure', [StripeController::class, 'failure'])->name('stripe.failure');

        Route::post('/become-a-vendor', [VendorController::class, 'store'])->name('vendor.store');

        Route::post('/stripe/connect', [StripeController::class, 'connect'])
            ->name('stripe.connect')
            ->middleware(['role:' . \App\Enums\RolesEnum::Vendor->value]);

        Route::get('/vendor/details', [VendorController::class, 'details'])
            ->name('vendor.details')
            ->middleware(['role:' . \App\Enums\RolesEnum::Vendor->value]);
    });
});

// Order invoice route for Filament
Route::get('/admin/orders/{order}/invoice', [\App\Http\Controllers\Admin\OrderController::class, 'invoice'])->name('filament.admin.resources.orders.invoice');

require __DIR__ . '/auth.php';

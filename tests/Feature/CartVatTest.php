<?php

use App\Enums\ProductStatusEnum;
use App\Enums\VendorStatusEnum;
use App\Models\Category;
use App\Models\Department;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Services\CartService;

it('calculates VAT based on vendor country', function () {
    // create vendor
    $vendorUser = User::factory()->create();
    Vendor::create([
        'user_id' => $vendorUser->id,
        'status' => VendorStatusEnum::Approved->value,
        'store_name' => 'Vendor',
        'country_code' => 'HU',
    ]);

    // create department and category
    $department = Department::create([
        'name' => 'Dept',
        'slug' => 'dept',
    ]);

    $category = Category::create([
        'name' => 'Cat',
        'department_id' => $department->id,
        'active' => true,
    ]);

    // create product
    $product = Product::create([
        'title' => 'Prod',
        'slug' => 'prod',
        'description' => 'desc',
        'department_id' => $department->id,
        'category_id' => $category->id,
        'price' => 100,
        'status' => ProductStatusEnum::Published->value,
        'quantity' => 10,
        'created_by' => $vendorUser->id,
        'updated_by' => $vendorUser->id,
        'vat_rate_type' => 'standard_rate',
    ]);

    // create customer
    $customer = User::factory()->create();
    actingAs($customer);

    $service = app(CartService::class);
    $service->addItemToCart($product, 1, []);

    $items = $service->getCartItems();
    expect($items)->toHaveCount(1);
    $item = $items[0];
    expect($item['vat_rate'])->toBe(27.0);
    expect($item['vat_amount'])->toBe(27.0);
    expect($item['gross_price'])->toBe(127.0);
    expect($service->getTotalVat())->toBe(27.0);
});

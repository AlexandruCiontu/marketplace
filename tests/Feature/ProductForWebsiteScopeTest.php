<?php

use App\Enums\ProductStatusEnum;
use App\Enums\VendorStatusEnum;
use App\Models\Category;
use App\Models\Department;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;

it('returns products with the correct id when using the forWebsite scope', function () {
    // Ensure vendor user id differs from product id
    User::factory()->create();

    $vendorUser = User::factory()->create();
    Vendor::create([
        'user_id' => $vendorUser->id,
        'status' => VendorStatusEnum::Approved->value,
        'store_name' => 'Vendor',
        'country_code' => 'RO',
    ]);

    $department = Department::create([
        'name' => 'Dept',
        'slug' => 'dept',
    ]);

    $category = Category::create([
        'name' => 'Cat',
        'department_id' => $department->id,
        'active' => true,
    ]);

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

    $found = Product::forWebsite()->first();

    expect($found)->not->toBeNull();
    expect($found->id)->toBe($product->id);
});

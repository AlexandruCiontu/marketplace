<?php

use App\Enums\OrderStatusEnum;
use App\Enums\ProductStatusEnum;
use App\Models\Category;
use App\Models\Department;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

it('allows verified buyers to post a review', function () {
    $buyer = User::factory()->create(['email_verified_at' => now()]);
    $vendor = User::factory()->create();

    $department = new Department();
    $department->name = 'Dept';
    $department->slug = 'dept';
    $department->save();

    $category = new Category();
    $category->name = 'Cat';
    $category->department_id = $department->id;
    $category->active = true;
    $category->save();

    $product = new Product();
    $product->title = 'Test';
    $product->slug = 'test';
    $product->description = 'desc';
    $product->department_id = $department->id;
    $product->category_id = $category->id;
    $product->price = 100;
    $product->status = ProductStatusEnum::Published->value;
    $product->quantity = 10;
    $product->created_by = $vendor->id;
    $product->updated_by = $vendor->id;
    $product->save();

    $order = Order::create([
        'total_price' => 100,
        'user_id' => $buyer->id,
        'vendor_user_id' => $vendor->id,
        'status' => OrderStatusEnum::Paid->value,
    ]);

    OrderItem::create([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'quantity' => 1,
        'net_price' => 100,
        'vat_rate' => 0,
        'vat_amount' => 0,
        'gross_price' => 100,
        'variation_type_option_ids' => [],
    ]);

    $this->actingAs($buyer)
        ->post(route('reviews.store', $product), ['rating' => 5, 'comment' => 'ok'])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect(DB::table('reviews')->where('user_id', $buyer->id)->where('product_id', $product->id)->exists())->toBeTrue();
});

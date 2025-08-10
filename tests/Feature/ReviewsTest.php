<?php

use App\Models\Category;
use App\Models\Department;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

it('allows verified buyers to post a review', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $seller = User::factory()->create();

    $department = Department::forceCreate([
        'name' => 'Dept',
        'slug' => 'dept',
        'active' => true,
    ]);

    $category = Category::forceCreate([
        'name' => 'Cat',
        'department_id' => $department->id,
        'active' => true,
    ]);

    $product = Product::forceCreate([
        'title' => 'Test',
        'slug' => 'test',
        'description' => 'desc',
        'department_id' => $department->id,
        'category_id' => $category->id,
        'price' => 100,
        'status' => 'published',
        'quantity' => 10,
        'created_by' => $seller->id,
        'updated_by' => $seller->id,
    ]);

    $order = Order::forceCreate([
        'total_price' => 100,
        'user_id' => $user->id,
        'vendor_user_id' => $seller->id,
        'status' => 'paid',
    ]);

    OrderItem::forceCreate([
        'order_id' => $order->id,
        'product_id' => $product->id,
        'price' => 100,
        'quantity' => 1,
    ]);

    $this->actingAs($user)
        ->post(route('reviews.store', $product), ['rating' => 5, 'comment' => 'ok'])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('reviews', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'rating' => 5,
    ]);
});

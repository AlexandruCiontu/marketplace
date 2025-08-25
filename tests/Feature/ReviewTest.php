<?php

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows verified buyer to post a review', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $product = Product::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'paid']);
    OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id]);

    $this->actingAs($user)
        ->post(route('products.reviews.store', $product), ['rating' => 5, 'body' => 'ok'])
        ->assertRedirect();

    $this->assertDatabaseHas('reviews', [
        'user_id' => $user->id,
        'product_id' => $product->id,
        'rating' => 5,
    ]);
});

it('blocks unverified user', function () {
    $user = User::factory()->create(['email_verified_at' => null]);
    $product = Product::factory()->create();

    $this->actingAs($user)
        ->post(route('products.reviews.store', $product), ['rating' => 5])
        ->assertForbidden();
});

it('blocks users who did not buy the product', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $product = Product::factory()->create();

    $this->actingAs($user)
        ->post(route('products.reviews.store', $product), ['rating' => 4])
        ->assertSessionHasErrors('review');
});

it('enforces one review per user per product', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);
    $product = Product::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'paid']);
    OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $product->id]);

    Review::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

    $this->actingAs($user)
        ->post(route('products.reviews.store', $product), ['rating' => 3])
        ->assertSessionHasErrors('review');
});

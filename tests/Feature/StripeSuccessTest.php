<?php

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('shows summary from session when session_id is missing', function () {
    $user = User::factory()->create();
    $vendor = User::factory()->create();

    $order = Order::create([
        'total_price' => 100,
        'user_id' => $user->id,
        'vendor_user_id' => $vendor->id,
        'status' => OrderStatusEnum::Paid->value,
        'net_total' => 80,
        'vat_total' => 20,
    ]);

    $response = $this->actingAs($user)
        ->withSession(['stripe_order_ids' => [$order->id]])
        ->get(route('stripe.success'));

    $response->assertStatus(200);

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Stripe/Success')
        ->where('orders.0.id', $order->id)
        ->where('sessionTotals.total', 100)
        ->where('sessionTotals.subtotal', 80)
        ->where('sessionTotals.tax', 20)
    );
});


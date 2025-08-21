<?php

use App\Models\User;

it('cannot checkout without a default address', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('cart.checkout'));

    $response->assertStatus(422);
    $response->assertSeeText('Shipping address is required.');
});

<?php

use Illuminate\Support\Facades\Session;

it('shows the vat country from the session', function () {
    Session::put('vat_country', 'DE');

    $response = $this->get('/api/vat-country');

    $response->assertStatus(200)
        ->assertJson(['country_code' => 'DE']);
});

it('updates the vat country in the session', function () {
    $response = $this->post('/api/vat-country', [
        'country_code' => 'HU',
    ]);

    $response->assertStatus(200)
        ->assertJson(['country_code' => 'HU']);

    $this->get('/api/vat-country')->assertJson(['country_code' => 'HU']);
});


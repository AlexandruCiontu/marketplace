<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('updates session country via API', function () {
    $this->post('/api/country/select', ['country_code' => 'ESP']);
    $this->get('/api/country/current')->assertJson(['country_code' => 'ES']);
});


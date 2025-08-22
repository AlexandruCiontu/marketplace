<?php

use App\Services\VatRateService;

it('calculates VAT for different countries', function () {
    $service = app(VatRateService::class);

    $ro = $service->calculate(100, 'standard', 'RO');
    expect($ro['gross'])->toBe(119.0)
        ->and($ro['vat'])->toBe(19.0)
        ->and($ro['country'])->toBe('RO');

    $bg = $service->calculate(100, 'standard', 'BG');
    expect($bg['gross'])->toBe(120.0)
        ->and($bg['vat'])->toBe(20.0);

    $hu = $service->calculate(100, 'standard', 'HU');
    expect($hu['gross'])->toBe(127.0)
        ->and($hu['vat'])->toBe(27.0);
});

it('normalizes country codes', function () {
    $service = app(VatRateService::class);

    $rou = $service->calculate(100, 'standard', 'ROU');
    expect($rou['country'])->toBe('RO');
});


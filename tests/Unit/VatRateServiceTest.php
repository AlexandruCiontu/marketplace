<?php

use App\Services\VatRateService;

it('calculates VAT for different countries', function () {
    $service = app(VatRateService::class);

    $roRate = $service->rateForProduct('standard', 'RO');
    $ro = $service->calculate(100, $roRate);
    expect($ro['price_gross'])->toBe(119.0)
        ->and($ro['vat_amount'])->toBe(19.0);

    $bgRate = $service->rateForProduct('standard', 'BG');
    $bg = $service->calculate(100, $bgRate);
    expect($bg['price_gross'])->toBe(120.0)
        ->and($bg['vat_amount'])->toBe(20.0);

    $huRate = $service->rateForProduct('standard', 'HU');
    $hu = $service->calculate(100, $huRate);
    expect($hu['price_gross'])->toBe(127.0)
        ->and($hu['vat_amount'])->toBe(27.0);
});

it('normalizes country codes', function () {
    $service = app(VatRateService::class);

    $rate = $service->rateForProduct('standard', 'ROU');
    $rou = $service->calculate(100, $rate);
    expect($rate)->toBe($service->rateForProduct('standard', 'RO'));
});


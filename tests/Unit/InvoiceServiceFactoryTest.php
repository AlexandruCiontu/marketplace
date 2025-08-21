<?php

use App\Models\Vendor;
use App\Services\VendorCountry\InvoiceServiceFactory;
use App\Services\VendorCountry\InvoiceServiceInterface;
use Tests\TestCase;

uses(TestCase::class);

test('factory resolves Romanian ANAF service', function () {
    $vendor = new Vendor(['country_code' => 'RO']);
    $service = app(InvoiceServiceFactory::class)->make($vendor);
    expect($service)->toBeInstanceOf(\App\Services\Romania\ANAFService::class);
    expect($service)->toBeInstanceOf(InvoiceServiceInterface::class);
});

test('factory resolves Hungarian NAV service', function () {
    $vendor = new Vendor(['country_code' => 'HU']);
    $service = app(InvoiceServiceFactory::class)->make($vendor);
    expect($service)->toBeInstanceOf(\App\Services\Hungary\NAVService::class);
    expect($service)->toBeInstanceOf(InvoiceServiceInterface::class);
});

test('factory resolves Bulgarian PDF service', function () {
    $vendor = new Vendor(['country_code' => 'BG']);
    $service = app(InvoiceServiceFactory::class)->make($vendor);
    expect($service)->toBeInstanceOf(\App\Services\VendorCountry\BG\InvoiceService::class);
    expect($service)->toBeInstanceOf(InvoiceServiceInterface::class);
});

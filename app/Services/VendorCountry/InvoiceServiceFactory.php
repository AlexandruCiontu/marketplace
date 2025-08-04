<?php

namespace App\Services\VendorCountry;

use App\Models\Vendor;
use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;

class InvoiceServiceFactory
{
    protected $countryServiceMap = [
        'RO' => \App\Services\Romania\ANAFService::class,
        'HU' => \App\Services\Hungary\NAVService::class,
        'BG' => \App\Services\VendorCountry\BG\InvoiceService::class,
    ];

    public function __construct(protected Container $container)
    {
    }

    public function make(Vendor $vendor): InvoiceServiceInterface
    {
        $countryCode = $vendor->country_code;

        if (!isset($this->countryServiceMap[$countryCode])) {
            throw new InvalidArgumentException("No invoice service available for country code: {$countryCode}");
        }

        $serviceClass = $this->countryServiceMap[$countryCode];

        return $this->container->make($serviceClass);
    }
}

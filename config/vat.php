<?php
return [
    'fallback_country' => env('VAT_FALLBACK_COUNTRY', 'RO'),
    'default_country' => env('VAT_DEFAULT_COUNTRY', 'RO'),
    'default_currency' => env('VAT_DEFAULT_CURRENCY', 'EUR'),
    'lock_to_default_address' => env('VAT_LOCK_TO_DEFAULT_ADDRESS', true),
    // Preferred rates per country; extend as needed
    'rates' => [
        'RO' => [
            'standard'      => 19.0,
            'reduced'       => 9.0,
            'reduced_alt'   => 5.0,
            'super_reduced' => 5.0,
            'zero'          => 0.0,
        ],
        'HU' => [
            'standard' => 27.0,
        ],
        'DE' => [
            'standard' => 19.0,
            'reduced'  => 7.0,
        ],
    ],
    // Fallback default rates when nothing else is available
    'default_rates' => [
        'standard'       => 19.0,
        'reduced'        => 10.0,
        'reduced_alt'    => 5.0,
        'super_reduced'  => 5.0,
        'zero'           => 0.0,
    ],
];

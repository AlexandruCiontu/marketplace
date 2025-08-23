<?php
return [
    'fallback_country' => env('VAT_FALLBACK_COUNTRY', 'RO'),
    'lock_to_default_address' => env('VAT_LOCK_TO_DEFAULT_ADDRESS', true),
    // Preferred rates per VAT type; extend as needed
    'rates' => [
        'standard' => [
            'RO' => 19.0,
            'HU' => 27.0,
        ],
        'reduced' => [
            'RO' => 9.0,
        ],
        // Second reduced rate
        'reduced_alt' => [
            // RO frequently uses 5% as the second reduced rate
            'RO' => 5.0,
        ],
        'super_reduced' => [
            'RO' => 5.0,
        ],
        'zero' => [
            'RO' => 0.0,
        ],
    ],
    // Fallback default rates when nothing else is available
    'default_rates' => [
        'standard'       => 21.0,
        'reduced'        => 10.0,
        'reduced_alt'    => 5.0,
        'super_reduced'  => 5.0,
        'zero'           => 0.0,
    ],
];

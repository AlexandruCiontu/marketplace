<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default VAT Country Code
    |--------------------------------------------------------------------------
    |
    | This value is used when a country code is not present in the session
    | during VAT calculations. You may configure it via the
    | VAT_DEFAULT_COUNTRY environment variable.
    |
    */

    'default_country_code' => env('VAT_DEFAULT_COUNTRY', 'RO'),

];

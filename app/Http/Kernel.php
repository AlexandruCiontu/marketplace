<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        // Middleware global (dacă vrei aici)
    ];

    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\DetectCountryFromGeoIP::class, // adăugat aici!
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\SyncVatCountryFromAddress::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'country' => \App\Http\Middleware\DetectCountryFromGeoIP::class, // opțional pentru rute
    ];
}

<?php

namespace App\Providers;

use App\Services\CartService;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Torann\GeoIP\Facades\GeoIP;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Listă de țări din Uniunea Europeană
     */
    private const EU_COUNTRIES = [
        'AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE',
        'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT',
        'RO', 'SK', 'SI', 'ES', 'SE'
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CartService::class, function () {
            return new CartService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ✅ Detectăm țara automat și o salvăm în sesiune
        if (!session()->has('country_code')) {
            try {
                $location = GeoIP::getLocation();
                $code = $location->iso_code ?? null;

                // Dacă nu e valid sau nu e țară UE, fallback la RO
                if (! $code || ! in_array($code, self::EU_COUNTRIES)) {
                    $code = 'RO';
                }

                session(['country_code' => $code]);
            } catch (\Exception $e) {
                session(['country_code' => 'RO']); // fallback complet
            }
        }

        // 🧾 Programare automată pentru payout
        Schedule::command('payout:vendors')
            ->monthlyOn(1, '00:00')
            ->withoutOverlapping();

        // ⚡ Optimizare vite
        Vite::prefetch(concurrency: 3);
    }
}

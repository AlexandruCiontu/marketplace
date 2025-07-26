<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @routes
    @unless(app()->environment('testing'))
        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/Pages/{$page['component']}.tsx"])
    @endunless
    @inertiaHead
</head>
<body class="font-sans antialiased">
@inertia

<script>
    window.vatService = {
        rates: {
            RO: { standard: 0.19, reduced: 0.09, reduced2: 0.05, zero: 0.0 },
            BG: { standard: 0.20, reduced: 0.09, reduced2: 0.0, zero: 0.0 },
            HU: { standard: 0.27, reduced: 0.18, reduced2: 0.05, zero: 0.0 },
        },
        calculate(basePrice, rateType = 'standard', country = 'RO') {
            const countryRates = this.rates[country] || this.rates['RO'];
            const rate = countryRates[rateType] ?? 0;
            const vat = basePrice * rate;
            const gross = basePrice + vat;
            return { gross, vat };
        }
    };

    window.countryCode = '{{ session('country_code', 'RO') }}';
</script>
</body>
</html>

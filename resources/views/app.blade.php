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
            RO: { standard_rate: 19, reduced_rate: 9, reduced_rate_alt: 5, super_reduced_rate: 0 },
            BG: { standard_rate: 20, reduced_rate: 9, reduced_rate_alt: 0, super_reduced_rate: 0 },
            HU: { standard_rate: 27, reduced_rate: 18, reduced_rate_alt: 5, super_reduced_rate: 0 },
        },
        calculate(basePrice, rateType = 'standard_rate', country = 'RO') {
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

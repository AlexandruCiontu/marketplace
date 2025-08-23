<?php

namespace App\Services;

use App\Models\Product;

class VatRateService
{
    protected array $rates;
    protected array $defaults;

    public function __construct()
    {
        $config = config('vat');
        $this->rates    = $config['rates'] ?? [];
        $this->defaults = $config['default_rates'] ?? [];
    }

    private function normalizeType(string $type): string
    {
        $t = \Illuminate\Support\Str::of($type)
            ->lower()
            ->replace([' ', '-', '.', '/'], '_')
            ->trim('_')
            ->value();

        return match ($t) {
            'super_reduced', 'superreduced' => 'super_reduced',
            'reduced_alt', 'reducedalt'     => 'reduced_alt',
            'reduced'                       => 'reduced',
            'zero', 'no_vat', 'none', '0'   => 'zero',
            default                         => 'standard',
        };
    }

    public function calculate(float $net, mixed $productOrType, string $country): array
    {
        $rate = $this->rateForProduct($productOrType, $country);
        $vat  = round($net * $rate / 100, 2);
        $gross = round($net + $vat, 2);

        return [
            'price_net'   => (float) $net,
            'vat_rate'    => (float) $rate,
            'vat_amount'  => (float) $vat,
            'price_gross' => (float) $gross,
        ];
    }

    public function rateForProduct(mixed $productOrType, string $country): float
    {
        $country = \Illuminate\Support\Str::upper($country);

        $type = is_string($productOrType)
            ? $productOrType
            : ($productOrType->vat_type ?? 'standard');

        $type = $this->normalizeType($type);

        $rates = $this->rates;
        $rate = $rates[$type][$country] ?? (
            $type === 'reduced_alt'
                ? ($rates['reduced'][$country] ?? null)
                : null
        );

        if ($rate === null) {
            $rate = $this->defaults[$type] ?? $this->defaults['standard'] ?? 19;
        }

        return (float) $rate;
    }
}


<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class VatRateService
{
    protected array $rates;
    protected array $defaults;

    public function __construct()
    {
        $config = config('vat');
        $this->rates    = $config['rates'] ?? [];
        $this->defaults = $config['default_rates'] ?? [];

        if (empty($this->rates) && Storage::disk('local')->exists('vat/rates.json')) {
            $json = json_decode(Storage::disk('local')->get('vat/rates.json'), true) ?: [];
            $this->rates = $json['rates'] ?? [];
            $this->defaults = $json['default_rates'] ?? $this->defaults;
        }
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
        $type = $productOrType instanceof Product
            ? $productOrType->vat_type
            : (string) $productOrType;

        $type = strtolower(preg_replace('/[^a-z]+/i', '_', $type));
        $type = trim($type, '_');
        $country = strtoupper($country);

        $rate = $this->rates[$type][$country] ?? null;

        if ($rate === null && $type === 'reduced_alt') {
            $rate = $this->rates['reduced'][$country] ?? null;
        }

        if ($rate === null) {
            $rate = $this->defaults[$type] ?? $this->defaults['standard'] ?? 19.0;
        }

        return (float) $rate;
    }
}


<?php

namespace App\Services;

use App\Models\Product;
use App\Support\CountryCode;

class VatRateService
{
    protected function normalizeType(?string $type): string
    {
        $t = strtolower(trim((string) $type));
        $t = str_replace([' ', '-'], '_', $t);
        return in_array($t, ['standard','reduced','reduced_alt','super_reduced','zero'], true)
            ? $t : 'standard';
    }

    public function rateForProduct(Product|string $productOrType, string $country): float
    {
        $country = strtoupper(CountryCode::toIso2($country) ?? 'RO');

        $type = $productOrType instanceof Product
            ? $this->normalizeType($productOrType->vat_type)
            : $this->normalizeType((string) $productOrType);

        $configured = config("vat.rates.$type.$country");
        if ($configured !== null) {
            return (float) $configured;
        }

        if ($type === 'reduced_alt') {
            $alt = config("vat.rates.reduced.$country");
            if ($alt !== null) {
                return (float) $alt;
            }
        }

        return (float) config("vat.default_rates.$type", config('vat.default_rates.standard', 21.0));
    }

    public function calculate(float $net, Product|string $productOrType, string $country): array
    {
        $rate  = $this->rateForProduct($productOrType, $country);
        $vat   = round($net * $rate / 100, 2);
        $gross = round($net + $vat, 2);

        return [
            'price_net'   => (float) $net,
            'vat_rate'    => (float) $rate,
            'vat_amount'  => (float) $vat,
            'price_gross' => (float) $gross,
        ];
    }
}

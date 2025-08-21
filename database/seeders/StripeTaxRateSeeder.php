<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class StripeTaxRateSeeder extends Seeder
{
    /**
     * Create Stripe TaxRate objects for supported countries.
     */
    public function run(): void
    {
        \Stripe\Stripe::setApiKey(config('app.stripe_secret_key'));

        $rates = [
            [
                'display_name' => 'VAT',
                'jurisdiction' => 'RO',
                'percentage' => 19,
                'inclusive' => true,
            ],
            [
                'display_name' => 'VAT',
                'jurisdiction' => 'BG',
                'percentage' => 20,
                'inclusive' => true,
            ],
            [
                'display_name' => 'VAT',
                'jurisdiction' => 'HU',
                'percentage' => 27,
                'inclusive' => true,
            ],
        ];

        foreach ($rates as $rate) {
            \Stripe\TaxRate::create($rate);
        }
    }
}


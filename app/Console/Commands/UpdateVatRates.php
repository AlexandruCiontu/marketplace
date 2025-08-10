<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ibericode\Vat\Rates;
use Ibericode\Vat\Client;

class UpdateVatRates extends Command
{
    protected $signature = 'app:update-vat-rates';

    protected $description = 'Fetch and cache the latest VAT rates from the EU official source.';

    public function handle()
    {
        $cachePath = storage_path('framework/cache/vat_rates.cache');

        // Initialize client and fetch data
        $client = new Client();
        $fetchedRates = $client->getRates();

        // Save to cache
        $rates = new Rates($cachePath);
        $rates->saveRates($fetchedRates);

        $this->info("VAT rates updated and cached successfully.");
    }
}

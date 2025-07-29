<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class RefreshVatRates extends Command
{
    protected $signature = 'vat:refresh';
    protected $description = 'Fetch and store latest VAT rates for EU countries';

    public function handle()
    {
        $url = 'https://jsonvat.com/';

        $this->info("⏳ Downloading VAT rates from {$url}...");

        try {
            $response = Http::get($url);

            if (!$response->successful()) {
                $this->error('❌ Failed to fetch VAT rates.');
                return 1;
            }

            Storage::put('vat-rates.json', $response->body());

            $this->info('✅ VAT rates updated successfully.');
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}


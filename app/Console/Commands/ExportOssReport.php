<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;

class ExportOssReport extends Command
{
    protected $signature = 'export:oss-report {month?} {year?}';

    protected $description = 'Export monthly VAT OSS report per vendor as CSV';

    public function handle(): int
    {
        $month = $this->argument('month') ?: now()->subMonth()->format('m');
        $year = $this->argument('year') ?: now()->format('Y');

        $orders = Order::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get(['vendor_user_id', 'vat_country_code', 'net_total', 'vat_total', 'total_price'])
            ->groupBy('vendor_user_id');

        foreach ($orders as $vendorId => $vendorOrders) {
            $csv = Writer::createFromString('');
            $csv->insertOne(['country_code', 'net_total', 'vat_amount', 'gross_total']);

            foreach ($vendorOrders as $order) {
                $csv->insertOne([
                    $order->vat_country_code,
                    $order->net_total,
                    $order->vat_total,
                    $order->total_price,
                ]);
            }

            $path = "exports/oss/{$year}-{$month}/{$vendorId}.csv";
            Storage::put($path, $csv->toString());
        }

        $this->info('Reports generated in storage/exports/oss');

        return Command::SUCCESS;
    }
}

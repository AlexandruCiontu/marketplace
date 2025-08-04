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
            ->where('transaction_type', 'OSS') // Filter for OSS transactions
            ->get(['vendor_user_id', 'vat_country_code', 'net_total', 'vat_total', 'total_price', 'created_at'])
            ->groupBy('vendor_user_id');

        foreach ($orders as $vendorId => $vendorOrders) {
            $csv = Writer::createFromString('');
            $csv->insertOne(['vendor_id', 'client_country', 'month', 'vat_amount', 'net_amount', 'gross_amount']);

            foreach ($vendorOrders as $order) {
                $csv->insertOne([
                    $vendorId,
                    $order->vat_country_code,
                    $order->created_at->format('Y-m'),
                    $order->vat_total,
                    $order->net_total,
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

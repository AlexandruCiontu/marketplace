<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;

class ExportOssReport extends Command
{
    protected $signature = 'vat:export-oss {month?} {year?}';

    protected $description = 'Export monthly VAT OSS report as CSV';

    public function handle(): int
    {
        $month = $this->argument('month') ?: now()->subMonth()->format('m');
        $year = $this->argument('year') ?: now()->format('Y');

        $orders = Order::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->get(['vat_country_code', 'net_total', 'vat_total', 'total_price']);

        $csv = Writer::createFromString('');
        $csv->insertOne(['country', 'net_total', 'vat_total', 'gross_total']);

        foreach ($orders as $order) {
            $csv->insertOne([
                $order->vat_country_code,
                $order->net_total,
                $order->vat_total,
                $order->total_price,
            ]);
        }

        $path = "oss-reports/{$year}-{$month}.csv";
        Storage::put($path, $csv->toString());

        $this->info('Report saved to storage/'.$path);

        return Command::SUCCESS;
    }
}

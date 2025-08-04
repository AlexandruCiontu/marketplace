<?php

namespace App\Console\Commands;

use App\Models\OssTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ExportOssReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:oss-report {--month=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export the OSS report for a given month';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $month = $this->option('month') ?: now()->format('Y-m');

        $transactions = OssTransaction::whereYear('created_at', substr($month, 0, 4))
            ->whereMonth('created_at', substr($month, 5, 2))
            ->get();

        if ($transactions->isEmpty()) {
            $this->info('No OSS transactions found for the selected month.');
            return;
        }

        $vendors = $transactions->groupBy('vendor_id');

        foreach ($vendors as $vendorId => $vendorTransactions) {
            $csvData = "Țară client,Total net,TVA,Total brut\n";

            foreach ($vendorTransactions as $transaction) {
                $csvData .= "{$transaction->client_country_code},{$transaction->net_amount},{$transaction->vat_amount},{$transaction->gross_amount}\n";
            }

            $fileName = "oss_reports/vendor_{$vendorId}/{$month}.csv";
            Storage::disk('private')->put($fileName, $csvData);
        }

        $this->info('OSS report exported successfully.');
    }
}

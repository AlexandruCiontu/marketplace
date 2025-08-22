<?php

use App\Enums\OrderStatusEnum;
use App\Enums\VendorStatusEnum;
use App\Models\Order;
use App\Models\OssTransaction;
use App\Models\User;
use App\Models\Vendor;
use App\Notifications\OssReportGenerated;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

it('exports oss report when transactions exist', function () {
    Storage::fake('public');
    Notification::fake();

    $vendorUser = User::factory()->create(['country_code' => 'RO']);

    $vendor = Vendor::create([
        'user_id' => $vendorUser->id,
        'status' => VendorStatusEnum::Approved->value,
        'store_name' => 'Vendor',
        'country_code' => 'RO',
    ]);

    $client = User::factory()->create(['country_code' => 'DE']);

    $order = Order::create([
        'stripe_session_id' => 'sess_1',
        'user_id' => $client->id,
        'vendor_user_id' => $vendorUser->id,
        'total_price' => 119,
        'net_total' => 100,
        'vat_total' => 19,
        'vat_country_code' => 'DE',
        'transaction_type' => 'OSS',
        'status' => OrderStatusEnum::Draft->value,
    ]);

    OssTransaction::create([
        'vendor_id' => $vendor->user_id,
        'order_id' => $order->id,
        'client_country_code' => 'DE',
        'vat_rate' => 19,
        'net_amount' => 100,
        'vat_amount' => 19,
        'gross_amount' => 119,
    ]);

    $month = now()->format('Y-m');

    $this->artisan('export:oss-report', ['--month' => $month])
        ->expectsOutput('OSS report exported successfully.')
        ->assertExitCode(0);

    Storage::disk('public')->assertExists("exports/oss/{$month}/{$vendor->user_id}.csv");
    Notification::assertSentTo($vendorUser, OssReportGenerated::class);
});


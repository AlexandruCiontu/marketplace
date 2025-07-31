<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('vat_country_code', 2)->nullable()->after('vendor_user_id');
            $table->decimal('net_total', 20, 4)->default(0)->after('total_price');
            $table->decimal('vat_total', 20, 4)->default(0)->after('net_total');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['vat_country_code', 'net_total', 'vat_total']);
        });
    }
};

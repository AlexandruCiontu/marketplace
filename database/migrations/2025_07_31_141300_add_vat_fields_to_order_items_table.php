<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('vat_rate', 6, 2)->nullable()->after('price');
            $table->decimal('vat_amount', 20, 4)->default(0)->after('vat_rate');
            $table->decimal('gross_price', 20, 4)->default(0)->after('vat_amount');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['vat_rate', 'vat_amount', 'gross_price']);
        });
    }
};

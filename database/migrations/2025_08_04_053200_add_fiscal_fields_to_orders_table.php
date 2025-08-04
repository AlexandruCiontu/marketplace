<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('transaction_type')->nullable()->after('status');
            $table->string('invoice_type')->nullable()->after('transaction_type');
            $table->string('invoice_storage_path')->nullable()->after('invoice_type');
            $table->boolean('included_in_oss')->default(false)->after('invoice_storage_path');
            $table->foreignId('refund_id')->nullable()->constrained('orders')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['refund_id']);
            $table->dropColumn([
                'transaction_type',
                'invoice_type',
                'invoice_storage_path',
                'included_in_oss',
                'refund_id'
            ]);
        });
    }
};

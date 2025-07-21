<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('weight', 10, 2)->nullable()->after('quantity');
            $table->decimal('length', 10, 2)->nullable()->after('weight');
            $table->decimal('width', 10, 2)->nullable()->after('length');
            $table->decimal('height', 10, 2)->nullable()->after('width');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['weight', 'length', 'width', 'height']);
        });
    }
};

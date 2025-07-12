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
        Schema::create('order_item_variation_type_option', function (Blueprint $table) {
            $table->foreignId('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->unsignedBigInteger('variation_type_option_id');
            $table->foreign('variation_type_option_id', 'fk_order_item_variation_type_option_variation_type_option_id')
                ->references('id')
                ->on('variation_type_options')
                ->onDelete('cascade');
            $table->primary(['order_item_id', 'variation_type_option_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_variation_type_option');
    }
};

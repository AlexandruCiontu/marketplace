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
        Schema::create('countries', function (Blueprint $table) {
            $table->char('code', 3)->primary();
            $table->string('name', 255);
            $table->boolean('active')->default(true);
            $table->json('states')->nullable();
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->morphs('addressable');
            $table->char('country_code', 3);
            $table->string('full_name', 255);
            $table->string('phone', 255);
            $table->string('city', 255);
            $table->string('type', 45);
            $table->string('zipcode', 255);
            $table->string('address1', 255);
            $table->string('address2', 255)->nullable();
            $table->string('state', 255)->nullable();
            $table->boolean('default')->default(false);
            $table->text('delivery_instructions')->nullable();

            // Foreign key to the 'countries' table
            $table->foreign('country_code')
                ->references('code')
                ->on('countries')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('countries');
    }
};

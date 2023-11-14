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
        Schema::create('pharmacy_item_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->nullable();
            $table->foreignId('common_condition_id')->nullable();
            $table->boolean('is_basic')->default(0);
            $table->foreignId('temp_product_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_item_details');
    }
};

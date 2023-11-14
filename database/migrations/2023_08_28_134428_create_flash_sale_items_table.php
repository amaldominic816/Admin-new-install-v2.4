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
        Schema::create('flash_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flash_sale_id');
            $table->foreignId('item_id');
            $table->integer('stock');
            $table->integer('sold')->default(0);
            $table->integer('available_stock');
            $table->string('discount_type');
            $table->double('discount', 23, 3)->default(0);
            $table->double('discount_amount', 23, 3)->default(0);
            $table->double('price', 23, 3)->default(0);
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flash_sale_items');
    }
};

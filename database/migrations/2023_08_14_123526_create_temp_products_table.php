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
        Schema::create('temp_products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('image',30)->nullable();
            $table->longText('images')->nullable();
            $table->foreignId('store_id');
            $table->foreignId('module_id');
            $table->foreignId('unit_id')->nullable();
            $table->foreignId('item_id')->nullable();
            $table->foreignId('category_id')->nullable();
            $table->string('category_ids',255)->nullable();
            $table->string('tag_ids',255)->nullable();
            $table->string('slug',255)->nullable();
            $table->text('variations')->nullable();
            $table->text('food_variations')->nullable();
            $table->string('add_ons')->nullable();
            $table->string('attributes',255)->nullable();
            $table->text('choice_options')->nullable();
            $table->decimal('price',24,2)->default(0);
            $table->decimal('tax',24,2)->default(0);
            $table->string('tax_type',20)->default('percent');
            $table->decimal('discount')->default(0);
            $table->string('discount_type',20)->default('percent');
            $table->boolean('veg')->default(0);
            $table->boolean('recommended')->default(0);
            $table->boolean('organic')->default(0);
            $table->foreignId('common_condition_id')->nullable();
            $table->boolean('basic')->default(0);
            $table->boolean('status')->default(1);
            $table->integer('stock')->default(0)->nullable();
            $table->integer('maximum_cart_quantity')->nullable();
            $table->text('note')->nullable();
            $table->boolean('is_rejected')->default(0);
            $table->time('available_time_ends')->nullable();
            $table->time('available_time_starts')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_products');
    }
};

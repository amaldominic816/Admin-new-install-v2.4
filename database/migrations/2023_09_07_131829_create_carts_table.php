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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('module_id');
            $table->foreignId('item_id');
            $table->boolean('is_guest')->default(0);
            $table->text('add_on_ids')->nullable();
            $table->text('add_on_qtys')->nullable();
            $table->string('item_type');
            $table->double('price', 24, 3);
            $table->integer('quantity');
            $table->text('variation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};

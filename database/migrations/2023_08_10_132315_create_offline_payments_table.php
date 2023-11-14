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
        Schema::create('offline_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id');
            $table->json('payment_info')->nullable();
            $table->string('status',10)->default('pending');
            $table->text('note')->nullable();
            $table->text('customer_note')->nullable();
            $table->text('method_fields')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offline_payments');
    }
};

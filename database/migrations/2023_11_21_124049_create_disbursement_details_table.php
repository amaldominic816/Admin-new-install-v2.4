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
        Schema::create('disbursement_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disbursement_id');
            $table->foreignId('store_id')->nullable();
            $table->foreignId('delivery_man_id')->nullable();
            $table->double('disbursement_amount', 23, 3)->default(0);
            $table->foreignId('payment_method');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disbursement_details');
    }
};

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
        Schema::create('disbursement_withdrawal_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable();
            $table->foreignId('delivery_man_id')->nullable();
            $table->foreignId('withdrawal_method_id');
            $table->string('method_name');
            $table->text('method_fields');
            $table->tinyInteger('is_default')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disbursement_withdrawal_methods');
    }
};

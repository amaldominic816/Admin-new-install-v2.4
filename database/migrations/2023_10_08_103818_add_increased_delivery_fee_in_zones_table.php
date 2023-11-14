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
        Schema::table('zones', function (Blueprint $table) {
            $table->double('increased_delivery_fee',8,2)->default('0');
            $table->boolean('increased_delivery_fee_status')->default('0');
            $table->string('increase_delivery_charge_message')->nullable();
            $table->boolean('offline_payment')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->dropColumn('increased_delivery_fee_status');
            $table->dropColumn('increased_delivery_fee');
            $table->dropColumn('increase_delivery_charge_message');
            $table->dropColumn('offline_payment');
        });
    }
};

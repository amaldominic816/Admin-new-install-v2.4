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
        Schema::table('withdraw_requests', function (Blueprint $table) {
            $table->foreignId('delivery_man_id')->nullable();
            $table->foreignId('withdrawal_method_id')->nullable();
            $table->json('withdrawal_method_fields')->nullable();
            $table->foreignId('vendor_id')->nullable()->change();
            $table->string('type',20)->default('manual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdraw_requests', function (Blueprint $table) {
            $table->dropColumn('delivery_man_id');
            $table->dropColumn('withdrawal_method_fields');
            $table->dropColumn('withdrawal_method_id');
            $table->dropColumn('type');
        });
    }
};

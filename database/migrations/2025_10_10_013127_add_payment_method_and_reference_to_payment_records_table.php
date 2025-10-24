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
        Schema::table('payment_records', function (Blueprint $table) {
            $table->string('payment_method', 50)->default('cash')->after('payment_status');
            $table->string('reference_number', 100)->nullable()->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_records', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'reference_number']);
        });
    }
};

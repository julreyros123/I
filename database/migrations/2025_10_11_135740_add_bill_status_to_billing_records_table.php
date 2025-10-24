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
        Schema::table('billing_records', function (Blueprint $table) {
            $table->string('bill_status')->default('Pending')->after('total_amount');
            $table->text('notes')->nullable()->after('bill_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billing_records', function (Blueprint $table) {
            $table->dropColumn(['bill_status', 'notes']);
        });
    }
};
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
            $table->decimal('advance_payment', 10, 2)->default(0)->after('service_fee');
            $table->decimal('overdue_penalty', 10, 2)->default(0)->after('advance_payment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billing_records', function (Blueprint $table) {
            $table->dropColumn(['advance_payment', 'overdue_penalty']);
        });
    }
};

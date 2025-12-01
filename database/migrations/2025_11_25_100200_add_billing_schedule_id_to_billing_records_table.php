<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_records', function (Blueprint $table) {
            if (!Schema::hasColumn('billing_records', 'billing_schedule_id')) {
                $table->unsignedBigInteger('billing_schedule_id')->nullable()->after('customer_id');
                $table->foreign('billing_schedule_id')
                    ->references('id')
                    ->on('billing_schedules')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('billing_records', function (Blueprint $table) {
            if (Schema::hasColumn('billing_records', 'billing_schedule_id')) {
                $table->dropForeign(['billing_schedule_id']);
                $table->dropColumn('billing_schedule_id');
            }
        });
    }
};

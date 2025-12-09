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
        Schema::table('reports', function (Blueprint $table) {
            if (!Schema::hasColumn('reports', 'report_type')) {
                $table->string('report_type')->default('system')->after('user_id');
            }

            if (!Schema::hasColumn('reports', 'customer_reference')) {
                $table->string('customer_reference')->nullable()->after('report_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (Schema::hasColumn('reports', 'customer_reference')) {
                $table->dropColumn('customer_reference');
            }

            if (Schema::hasColumn('reports', 'report_type')) {
                $table->dropColumn('report_type');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customer_applications', function (Blueprint $table) {
            $table->unsignedInteger('score')->nullable()->after('status');
            $table->json('score_breakdown')->nullable()->after('score');
            $table->string('risk_level')->nullable()->after('score_breakdown');
            $table->enum('decision', ['approve','review','reject'])->nullable()->after('risk_level');
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('decision');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->json('risk_flags')->nullable()->after('reviewed_at');

            $table->index(['decision']);
            $table->index(['risk_level']);
            $table->index(['reviewed_by']);
        });
    }

    public function down(): void
    {
        Schema::table('customer_applications', function (Blueprint $table) {
            $table->dropIndex(['decision']);
            $table->dropIndex(['risk_level']);
            $table->dropIndex(['reviewed_by']);
            $table->dropColumn(['score','score_breakdown','risk_level','decision','reviewed_by','reviewed_at','risk_flags']);
        });
    }
};

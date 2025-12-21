<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_records', function (Blueprint $table) {
            if (!Schema::hasColumn('billing_records', 'pdf_path')) {
                $table->string('pdf_path')->nullable()->after('account_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('billing_records', function (Blueprint $table) {
            if (Schema::hasColumn('billing_records', 'pdf_path')) {
                $table->dropColumn('pdf_path');
            }
        });
    }
};

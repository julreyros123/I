<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_records', function (Blueprint $table) {
            $table->timestamp('backed_up_at')->nullable()->after('pdf_path');
            $table->string('backup_status')->default('pending')->after('backed_up_at');
        });
    }

    public function down(): void
    {
        Schema::table('billing_records', function (Blueprint $table) {
            $table->dropColumn(['backed_up_at', 'backup_status']);
        });
    }
};

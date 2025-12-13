<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->timestamp('reconnect_requested_at')->nullable()->after('status');
            $table->foreignId('reconnect_requested_by')->nullable()->after('reconnect_requested_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['reconnect_requested_by']);
            $table->dropColumn(['reconnect_requested_at', 'reconnect_requested_by']);
        });
    }
};

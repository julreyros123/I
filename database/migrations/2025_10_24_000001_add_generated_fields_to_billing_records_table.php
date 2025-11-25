<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('billing_records', function (Blueprint $table) {
            $table->boolean('is_generated')->default(false)->after('bill_status');
            $table->timestamp('generated_at')->nullable()->after('is_generated');
        });
    }

    public function down(): void
    {
        Schema::table('billing_records', function (Blueprint $table) {
            $table->dropColumn(['is_generated', 'generated_at']);
        });
    }
};

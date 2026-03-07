\<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!collect(\DB::select("SHOW INDEX FROM customers WHERE Key_name = 'customers_account_no_unique'"))->count()) {
                $table->unique('account_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['account_no']);
        });
    }
};
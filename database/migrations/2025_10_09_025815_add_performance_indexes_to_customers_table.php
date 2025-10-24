<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Add composite index for status and account_no for faster filtering
            $table->index(['status', 'account_no'], 'idx_customers_status_account');
            
            // Add index for name searches
            $table->index('name', 'idx_customers_name');
            
            // Add index for address searches
            $table->index('address', 'idx_customers_address');
            
            // Add index for meter_no searches
            $table->index('meter_no', 'idx_customers_meter_no');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex('idx_customers_status_account');
            $table->dropIndex('idx_customers_name');
            $table->dropIndex('idx_customers_address');
            $table->dropIndex('idx_customers_meter_no');
        });
    }
};
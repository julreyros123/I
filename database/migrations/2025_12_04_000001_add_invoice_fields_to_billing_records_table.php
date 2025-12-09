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
            $table->string('invoice_number')->nullable()->after('account_no');
            $table->string('prepared_by')->nullable()->after('invoice_number');
            $table->timestamp('issued_at')->nullable()->after('due_date');

            $table->unique('invoice_number', 'billing_records_invoice_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billing_records', function (Blueprint $table) {
            if (Schema::hasColumn('billing_records', 'invoice_number')) {
                $table->dropUnique('billing_records_invoice_number_unique');
                $table->dropColumn('invoice_number');
            }

            if (Schema::hasColumn('billing_records', 'prepared_by')) {
                $table->dropColumn('prepared_by');
            }

            if (Schema::hasColumn('billing_records', 'issued_at')) {
                $table->dropColumn('issued_at');
            }
        });
    }
};

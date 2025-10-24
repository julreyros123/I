<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('billing_record_id')->nullable()->constrained('billing_records')->nullOnDelete();
            $table->string('account_no')->index();
            $table->decimal('bill_amount', 10, 2); // Original bill amount
            $table->decimal('amount_paid', 10, 2); // Actual amount paid by customer
            $table->decimal('overpayment', 10, 2)->default(0); // Excess amount (if any)
            $table->decimal('credit_applied', 10, 2)->default(0); // Credit used from previous overpayments
            $table->enum('payment_status', ['paid', 'partial', 'overpaid'])->default('paid');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['account_no', 'created_at']);
            $table->index('payment_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_records');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('applicant_payment_records')) {
            return;
        }
        Schema::create('applicant_payment_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_application_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_number', 50)->unique();
            $table->decimal('amount_due', 10, 2);
            $table->decimal('amount_tendered', 10, 2);
            $table->decimal('change_given', 10, 2)->default(0);
            $table->json('fee_breakdown')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['customer_application_id', 'invoice_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_payment_records');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('applicant_name');
            $table->string('address')->nullable();
            $table->string('contact_no')->nullable();
            $table->enum('status', ['registered','inspected','approved','assessed','paid','scheduled','installed','rejected'])->default('registered');
            $table->json('documents')->nullable();
            // inspection
            $table->date('inspection_date')->nullable();
            $table->unsignedBigInteger('inspected_by')->nullable();
            $table->text('inspection_notes')->nullable();
            // approval / assessment
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->decimal('fee_application', 12, 2)->nullable();
            $table->decimal('fee_inspection', 12, 2)->nullable();
            $table->decimal('fee_materials', 12, 2)->nullable();
            $table->decimal('fee_labor', 12, 2)->nullable();
            $table->decimal('meter_deposit', 12, 2)->nullable();
            $table->decimal('fee_total', 12, 2)->nullable();
            // payment
            $table->string('payment_receipt_no')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('paid_by')->nullable();
            // scheduling / installation
            $table->date('schedule_date')->nullable();
            $table->unsignedBigInteger('scheduled_by')->nullable();
            $table->timestamp('installed_at')->nullable();
            $table->unsignedBigInteger('installed_by')->nullable();
            // audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['customer_id']);
            $table->index(['created_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_applications');
    }
};

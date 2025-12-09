<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meter_service_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meter_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_application_id')->nullable()->constrained()->nullOnDelete();
            $table->string('issue_type', 100);
            $table->text('description')->nullable();
            $table->string('status', 40)->default('open');
            $table->timestamp('scheduled_visit_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['meter_id']);
            $table->index(['customer_id']);
            $table->index(['customer_application_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meter_service_tickets');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customer_issue_reports')) {
            Schema::create('customer_issue_reports', function (Blueprint $table) {
                $table->id();
                $table->string('account_no', 32);
                $table->string('customer_name')->nullable();
                $table->string('contact_number')->nullable();
                $table->string('issue_type');
                $table->string('severity')->default('normal');
                $table->string('channel')->nullable();
                $table->string('subject')->nullable();
                $table->text('summary');
                $table->longText('details')->nullable();
                $table->string('status')->default('open');
                $table->boolean('is_priority')->default(false);
                $table->foreignId('documented_by')->constrained('users')->cascadeOnDelete();
                $table->timestamp('acknowledged_at')->nullable();
                $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('resolved_at')->nullable();
                $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('resolution_notes')->nullable();
                $table->timestamps();

                $table->index(['account_no']);
                $table->index(['status']);
                $table->index(['is_priority']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_issue_reports');
    }
};

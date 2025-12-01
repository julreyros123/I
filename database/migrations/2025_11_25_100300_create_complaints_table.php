<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('complaints')) {
            Schema::create('complaints', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
                $table->string('account_no')->nullable();
                $table->string('category')->nullable();
                $table->text('description');
                $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
                $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
                $table->dateTime('reported_at');
                $table->dateTime('first_response_at')->nullable();
                $table->dateTime('resolved_at')->nullable();
                $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('resolution_details')->nullable();
                $table->string('source')->nullable();
                $table->timestamps();

                $table->index(['account_no', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};

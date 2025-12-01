<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('billing_schedules')) {
            Schema::create('billing_schedules', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->date('period_start');
                $table->date('period_end');
                $table->dateTime('run_date')->nullable();
                $table->date('due_date')->nullable();
                $table->enum('status', ['planned', 'running', 'completed', 'cancelled'])->default('planned');
                $table->unsignedInteger('total_bills')->default(0);
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_schedules');
    }
};

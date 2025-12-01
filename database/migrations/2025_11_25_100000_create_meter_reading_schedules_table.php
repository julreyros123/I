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
        if (!Schema::hasTable('meter_reading_schedules')) {
            Schema::create('meter_reading_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
                $table->string('account_no');
                $table->date('scheduled_date');
                $table->dateTime('actual_reading_date')->nullable();
                $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
                $table->unsignedBigInteger('billing_record_id')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('billing_record_id')->references('id')->on('billing_records')->nullOnDelete();
                $table->index(['account_no', 'scheduled_date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meter_reading_schedules');
    }
};

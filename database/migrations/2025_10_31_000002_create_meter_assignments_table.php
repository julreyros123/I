<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('meter_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meter_id');
            $table->unsignedBigInteger('account_id');
            $table->dateTime('assigned_at');
            $table->dateTime('unassigned_at')->nullable();
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->unsignedBigInteger('unassigned_by')->nullable();
            $table->timestamps();

            $table->index(['meter_id']);
            $table->index(['account_id']);
            $table->index(['assigned_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('meter_assignments');
    }
};

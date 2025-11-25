<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('meter_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meter_id');
            $table->string('action'); // create, update, status_change, assign, unassign, import
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->json('from_json')->nullable();
            $table->json('to_json')->nullable();
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['meter_id']);
            $table->index(['action']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('meter_audits');
    }
};

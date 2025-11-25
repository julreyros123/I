<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('meters', function (Blueprint $table) {
            $table->id();
            $table->string('serial')->unique();
            $table->string('qr_code')->nullable();
            $table->string('type')->nullable();
            $table->string('size')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('seal_no')->nullable();
            $table->enum('status', ['inventory','installed','active','maintenance','inactive','retired'])->default('inventory');
            $table->date('install_date')->nullable();
            $table->string('location_address')->nullable();
            $table->string('barangay')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->decimal('last_reading_value', 12, 2)->nullable();
            $table->dateTime('last_reading_at')->nullable();
            $table->unsignedBigInteger('current_account_id')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['barangay']);
            $table->index(['current_account_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('meters');
    }
};

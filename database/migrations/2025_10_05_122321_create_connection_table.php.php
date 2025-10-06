<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('cascade');
            $table->string('meter_no')->nullable();
            $table->string('meter_size')->nullable();
            $table->decimal('previous_reading', 8, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->enum('status', ['Connected', 'Disconnected'])->default('Connected');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('connections');
    }
};

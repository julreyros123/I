<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('account_no')->index();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('meter_no')->nullable();
            $table->string('meter_size')->nullable();
            $table->string('status')->default('Active');
            $table->decimal('previous_reading', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};



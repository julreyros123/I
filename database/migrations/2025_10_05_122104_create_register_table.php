<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('register', function (Blueprint $table) {
            $table->id();
            $table->string('account_no')->unique(); // Remove nullable, make it required
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('contact_no', 50)->nullable();
            $table->string('connection_classification')->nullable(); // residential, commercial, etc.
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('register');
    }
};

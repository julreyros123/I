<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('area_code', 10);
            $table->string('route_code', 10);
            $table->integer('last_number')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_sequences');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id');
            $table->unsignedBigInteger('staff_id');
            $table->enum('type', ['computed','generated','printed','delivered','corrected']);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['bill_id','type']);
            $table->index(['staff_id','created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_events');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_progresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id');
            $table->date('date');
            $table->unsignedSmallInteger('target')->default(0);
            $table->unsignedSmallInteger('completed')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['staff_id','date']);
            $table->index(['staff_id','date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_progresses');
    }
};

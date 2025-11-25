<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->string('account_no');
            $table->unsignedBigInteger('cycle_id')->nullable();
            $table->decimal('usage', 10, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('penalties', 12, 2)->default(0);
            $table->decimal('advances', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->enum('status', ['draft','generated','printed','delivered','corrected'])->default('draft');
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->unsignedBigInteger('staff_id')->nullable(); // last updated by
            $table->timestamps();

            $table->index(['account_no']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};

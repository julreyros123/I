<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billings', function (Blueprint $table) {
            $table->id();
            $table->string('account_no');
            $table->string('customer_name');
            $table->decimal('previous_reading', 8, 2)->default(0);
            $table->decimal('current_reading', 8, 2)->default(0);
            $table->decimal('consumption', 8, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->date('billing_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status')->default('Unpaid'); // Paid, Unpaid, Overdue
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};

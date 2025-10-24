<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transfer_reconnect_audits', function (Blueprint $table) {
            $table->id();
            $table->string('account_no', 50)->index();
            $table->string('action', 50); // transfer or reconnect
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->timestamp('performed_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transfer_reconnect_audits');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('customer_applications')
            ->where('status', 'assessed')
            ->update(['status' => 'waiting_payment']);

        DB::statement(<<<SQL
            ALTER TABLE customer_applications
            MODIFY status ENUM(
                'registered',
                'inspected',
                'approved',
                'waiting_payment',
                'paid',
                'scheduled',
                'installing',
                'installed',
                'rejected'
            ) NOT NULL DEFAULT 'registered'
        SQL);
    }

    public function down(): void
    {
        DB::table('customer_applications')
            ->where('status', 'waiting_payment')
            ->update(['status' => 'assessed']);

        DB::statement(<<<SQL
            ALTER TABLE customer_applications
            MODIFY status ENUM(
                'registered',
                'inspected',
                'approved',
                'assessed',
                'paid',
                'scheduled',
                'installed',
                'rejected'
            ) NOT NULL DEFAULT 'registered'
        SQL);
    }
};

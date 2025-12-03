<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (!Schema::hasColumn('reports', 'status')) {
                $table->string('status')->default('open')->after('other_problem');
            }
            if (!Schema::hasColumn('reports', 'is_priority')) {
                $table->boolean('is_priority')->default(false)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (Schema::hasColumn('reports', 'is_priority')) {
                $table->dropColumn('is_priority');
            }
            if (Schema::hasColumn('reports', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};

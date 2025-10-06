<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (!Schema::hasColumn('reports', 'category')) {
                $table->string('category')->nullable()->after('message');
            }
            if (!Schema::hasColumn('reports', 'other_problem')) {
                $table->string('other_problem')->nullable()->after('category');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (Schema::hasColumn('reports', 'other_problem')) {
                $table->dropColumn('other_problem');
            }
            if (Schema::hasColumn('reports', 'category')) {
                $table->dropColumn('category');
            }
        });
    }
};



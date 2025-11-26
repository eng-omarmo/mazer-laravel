<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'salary')) {
                $table->decimal('salary', 12, 2)->nullable()->after('position');
            }
            if (! Schema::hasColumn('employees', 'bonus')) {
                $table->decimal('bonus', 12, 2)->nullable()->after('salary');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'bonus')) {
                $table->dropColumn('bonus');
            }
            if (Schema::hasColumn('employees', 'salary')) {
                $table->dropColumn('salary');
            }
        });
    }
};

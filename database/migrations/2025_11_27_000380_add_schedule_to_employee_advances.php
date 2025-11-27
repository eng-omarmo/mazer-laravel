<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_advances', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_advances', 'next_due_date')) {
                $table->date('next_due_date')->nullable()->after('installment_amount');
            }
            if (! Schema::hasColumn('employee_advances', 'schedule_type')) {
                $table->enum('schedule_type', ['none', 'weekly', 'biweekly', 'monthly'])->default('none')->after('next_due_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_advances', function (Blueprint $table) {
            if (Schema::hasColumn('employee_advances', 'schedule_type')) {
                $table->dropColumn('schedule_type');
            }
            if (Schema::hasColumn('employee_advances', 'next_due_date')) {
                $table->dropColumn('next_due_date');
            }
        });
    }
};

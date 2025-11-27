<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_advances', function (Blueprint $table) {
            if (! Schema::hasColumn('employee_advances', 'remaining_balance')) {
                $table->decimal('remaining_balance', 12, 2)->default(0)->after('amount');
            }
            if (! Schema::hasColumn('employee_advances', 'installment_amount')) {
                $table->decimal('installment_amount', 12, 2)->nullable()->after('remaining_balance');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_advances', function (Blueprint $table) {
            if (Schema::hasColumn('employee_advances', 'installment_amount')) {
                $table->dropColumn('installment_amount');
            }
            if (Schema::hasColumn('employee_advances', 'remaining_balance')) {
                $table->dropColumn('remaining_balance');
            }
        });
    }
};

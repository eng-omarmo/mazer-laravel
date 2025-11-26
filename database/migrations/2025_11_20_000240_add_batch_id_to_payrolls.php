<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (! Schema::hasColumn('payrolls', 'batch_id')) {
                $table->foreignId('batch_id')->nullable()->constrained('payroll_batches')->cascadeOnDelete()->after('id');
                $table->index('batch_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            if (Schema::hasColumn('payrolls', 'batch_id')) {
                $table->dropConstrainedForeignId('batch_id');
            }
        });
    }
};

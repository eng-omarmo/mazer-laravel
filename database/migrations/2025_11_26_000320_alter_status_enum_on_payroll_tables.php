<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payroll_batches')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE payroll_batches MODIFY COLUMN status ENUM('draft','submitted','approved','rejected','paid') NOT NULL DEFAULT 'draft'");
            }
        }

        if (Schema::hasTable('payrolls')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE payrolls MODIFY COLUMN status ENUM('draft','approved','paid') NOT NULL DEFAULT 'draft'");
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payroll_batches')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE payroll_batches MODIFY COLUMN status ENUM('draft','submitted','approved','rejected') NOT NULL DEFAULT 'draft'");
            }
        }

        if (Schema::hasTable('payrolls')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE payrolls MODIFY COLUMN status ENUM('draft','approved') NOT NULL DEFAULT 'draft'");
            }
        }
    }
};

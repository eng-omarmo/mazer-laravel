<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','hrm','finance','credit_manager') NOT NULL DEFAULT 'hrm'");
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','hrm','finance') NOT NULL DEFAULT 'hrm'");
            }
        }
    }
};

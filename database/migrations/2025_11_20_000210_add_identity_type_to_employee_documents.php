<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('employee_documents')) {
            DB::statement("ALTER TABLE employee_documents MODIFY COLUMN type ENUM('cv','contract','identity') NOT NULL");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('employee_documents')) {
            DB::statement("ALTER TABLE employee_documents MODIFY COLUMN type ENUM('cv','contract') NOT NULL");
        }
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'fingerprint_id')) {
                $table->string('fingerprint_id', 100)->nullable()->unique()->after('identity_doc_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'fingerprint_id')) {
                $table->dropUnique(['fingerprint_id']);
                $table->dropColumn('fingerprint_id');
            }
        });
    }
};

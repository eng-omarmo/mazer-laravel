<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'reference_full_name')) {
                $table->string('reference_full_name')->nullable()->after('bonus');
            }
            if (! Schema::hasColumn('employees', 'reference_phone')) {
                $table->string('reference_phone', 30)->nullable()->after('reference_full_name');
            }
            if (! Schema::hasColumn('employees', 'identity_doc_number')) {
                $table->string('identity_doc_number', 100)->nullable()->after('reference_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'identity_doc_number')) {
                $table->dropColumn('identity_doc_number');
            }
            if (Schema::hasColumn('employees', 'reference_phone')) {
                $table->dropColumn('reference_phone');
            }
            if (Schema::hasColumn('employees', 'reference_full_name')) {
                $table->dropColumn('reference_full_name');
            }
        });
    }
};

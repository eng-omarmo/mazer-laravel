<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('attendance_logs', 'shift_type')) {
                $table->enum('shift_type', ['morning', 'afternoon'])->nullable()->after('source');
            }
            if (! Schema::hasColumn('attendance_logs', 'expected_check_out')) {
                $table->time('expected_check_out')->nullable()->after('shift_type');
            }
            if (! Schema::hasColumn('attendance_logs', 'overtime_minutes')) {
                $table->unsignedInteger('overtime_minutes')->default(0)->after('expected_check_out');
            }
            if (! Schema::hasColumn('attendance_logs', 'early_arrival')) {
                $table->boolean('early_arrival')->default(false)->after('overtime_minutes');
            }
            if (! Schema::hasColumn('attendance_logs', 'late_checkin')) {
                $table->boolean('late_checkin')->default(false)->after('early_arrival');
            }
            if (! Schema::hasColumn('attendance_logs', 'missed_checkout')) {
                $table->boolean('missed_checkout')->default(false)->after('late_checkin');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            if (Schema::hasColumn('attendance_logs', 'missed_checkout')) {
                $table->dropColumn('missed_checkout');
            }
            if (Schema::hasColumn('attendance_logs', 'late_checkin')) {
                $table->dropColumn('late_checkin');
            }
            if (Schema::hasColumn('attendance_logs', 'early_arrival')) {
                $table->dropColumn('early_arrival');
            }
            if (Schema::hasColumn('attendance_logs', 'overtime_minutes')) {
                $table->dropColumn('overtime_minutes');
            }
            if (Schema::hasColumn('attendance_logs', 'expected_check_out')) {
                $table->dropColumn('expected_check_out');
            }
            if (Schema::hasColumn('attendance_logs', 'shift_type')) {
                $table->dropColumn('shift_type');
            }
        });
    }
};

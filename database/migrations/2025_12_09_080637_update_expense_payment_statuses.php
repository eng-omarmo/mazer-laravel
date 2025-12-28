<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update expenses table
        Schema::table('expenses', function (Blueprint $table) {
            if (! Schema::hasColumn('expenses', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'partial', 'paid'])->default('pending')->after('amount');
            }
        });

        // Update expense_payments table
        Schema::table('expense_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('expense_payments', 'status')) {
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('amount');
            }
        });

        // Migrate existing data if any (Assuming MySQL/SQLite compatibility)
        // For expense_payments: is_approved=1 -> status='approved', else 'pending'
        if (Schema::hasColumn('expense_payments', 'is_approved')) {
            DB::table('expense_payments')->where('is_approved', true)->update(['status' => 'approved']);
            DB::table('expense_payments')->where('is_approved', false)->update(['status' => 'pending']);

            Schema::table('expense_payments', function (Blueprint $table) {
                $table->dropColumn('is_approved');
            });
        }

        // Recalculate payment_status for existing expenses is a bit complex in migration without models,
        // but we can set default to pending.
        // Ideally we should run a seeder or manual update, but for now default 'pending' is safe.
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'payment_status')) {
                $table->dropColumn('payment_status');
            }
        });

        Schema::table('expense_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('expense_payments', 'is_approved')) {
                $table->boolean('is_approved')->default(false);
            }
        });

        // Restore is_approved based on status
        DB::table('expense_payments')->where('status', 'approved')->update(['is_approved' => true]);
        DB::table('expense_payments')->where('status', '!=', 'approved')->update(['is_approved' => false]);

        Schema::table('expense_payments', function (Blueprint $table) {
            if (Schema::hasColumn('expense_payments', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};

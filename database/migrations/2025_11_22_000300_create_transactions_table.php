<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('direction', ['credit','debit']);
            $table->string('type', 50);
            $table->decimal('amount', 14, 2);
            $table->string('reference')->nullable();
            $table->foreignId('batch_id')->nullable()->constrained('payroll_batches')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->unsignedBigInteger('posted_by')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->enum('status', ['draft','posted','failed'])->default('posted');
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['direction','type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advance_id')->constrained('employee_advances')->cascadeOnDelete();
            $table->enum('type', ['grant', 'repayment']);
            $table->decimal('amount', 12, 2);
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->index(['advance_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advance_transactions');
    }
};

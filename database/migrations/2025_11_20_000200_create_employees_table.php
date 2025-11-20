<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id('employee_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->date('dob')->nullable();
            $table->enum('gender', ['male','female','other'])->nullable();
            $table->unsignedBigInteger('department_id');
            $table->string('designation')->nullable();
            $table->date('join_date')->nullable();
            $table->enum('employment_type', ['full-time','part-time','contract']);
            $table->enum('status', ['active','resigned','terminated'])->default('active');
            $table->timestamps();
            $table->foreign('department_id')->references('department_id')->on('departments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biometric_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('device_sn')->nullable();
            $table->string('algorithm')->nullable();
            $table->unsignedInteger('dpi')->nullable();
            $table->unsignedInteger('quality_score')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->text('ciphertext'); // base64
            $table->string('iv'); // base64
            $table->string('tag'); // base64 (for AES-GCM)
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'captured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biometric_templates');
    }
};

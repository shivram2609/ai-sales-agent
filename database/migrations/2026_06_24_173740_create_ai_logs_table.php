<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_logs', function (Blueprint $table) {
            $table->id();

            $table->string('loggable_type', 191)->nullable();
            $table->unsignedBigInteger('loggable_id')->nullable();

            $table->string('action', 100);
            $table->string('model', 100)->nullable();
            $table->string('prompt_version', 50)->nullable();

            $table->json('input_snapshot')->nullable();
            $table->json('output_json')->nullable();

            $table->string('status', 50)->default('pending');
            $table->longText('error_message')->nullable();

            $table->timestamps();

            $table->index(['loggable_type', 'loggable_id']);
            $table->index('action');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_logs');
    }
};
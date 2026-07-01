<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospect_contacts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('prospect_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name', 190)->nullable();
            $table->string('email', 190)->nullable();
            $table->string('title', 190)->nullable();
            $table->string('linkedin_url', 500)->nullable();
            $table->string('phone', 80)->nullable();

            $table->string('source', 80)->default('manual');
            $table->string('status', 80)->default('active');

            $table->boolean('is_primary')->default(false);
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['prospect_id', 'email']);
            $table->index(['prospect_id', 'is_primary']);
            $table->index(['prospect_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospect_contacts');
    }
};
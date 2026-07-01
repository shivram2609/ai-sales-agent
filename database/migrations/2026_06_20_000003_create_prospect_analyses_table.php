<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prospect_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained()->cascadeOnDelete();
            $table->string('company_type', 100)->nullable();
            $table->json('primary_services_json')->nullable();
            $table->json('target_clients_json')->nullable();
            $table->string('visible_technical_depth', 50)->nullable();
            $table->text('likely_gap')->nullable();
            $table->text('partnership_angle')->nullable();
            $table->string('region', 100)->nullable();
            $table->integer('confidence')->default(0);
            $table->integer('rule_score')->default(0);
            $table->integer('ai_score')->default(0);
            $table->integer('final_score')->default(0)->index();
            $table->string('recommended_action', 50)->nullable();
            $table->text('reasoning_summary')->nullable();
            $table->json('raw')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('prospect_analyses'); }
};

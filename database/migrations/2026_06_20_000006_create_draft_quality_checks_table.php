<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('draft_quality_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outreach_draft_id')->constrained()->cascadeOnDelete();
            $table->boolean('has_fake_personalization')->default(false);
            $table->boolean('has_misleading_claim')->default(false);
            $table->boolean('too_salesy')->default(false);
            $table->boolean('too_long')->default(false);
            $table->boolean('missing_opt_out')->default(false);
            $table->boolean('too_many_links')->default(false);
            $table->string('risk_level', 50)->default('low')->index();
            $table->json('fixes_required')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('draft_quality_checks'); }
};

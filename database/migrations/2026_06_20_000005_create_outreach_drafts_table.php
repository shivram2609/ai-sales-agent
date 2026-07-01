<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('outreach_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 50)->default('email_linkedin');
            $table->string('subject', 500)->nullable();
            $table->longText('body')->nullable();
            $table->text('linkedin_connection_note')->nullable();
            $table->text('linkedin_followup')->nullable();
            $table->longText('follow_up_1')->nullable();
            $table->longText('follow_up_2')->nullable();
            $table->json('proof_links_used')->nullable();
            $table->json('personalization_notes')->nullable();
            $table->json('risk_flags')->nullable();
            $table->string('status', 50)->default('drafted')->index();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('outreach_drafts'); }
};

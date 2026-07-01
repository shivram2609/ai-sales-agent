<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outreach_drafts', function (Blueprint $table) {
            $table->unsignedTinyInteger('ai_quality_score')->nullable()->after('status');
            $table->unsignedTinyInteger('ai_personalization_score')->nullable()->after('ai_quality_score');
            $table->unsignedTinyInteger('ai_relevance_score')->nullable()->after('ai_personalization_score');
            $table->unsignedTinyInteger('ai_proof_score')->nullable()->after('ai_relevance_score');
            $table->unsignedTinyInteger('ai_spam_risk_score')->nullable()->after('ai_proof_score');

            $table->longText('ai_quality_notes')->nullable()->after('ai_spam_risk_score');
            $table->longText('ai_improvement_notes')->nullable()->after('ai_quality_notes');

            $table->string('ai_model_used', 100)->nullable()->after('ai_improvement_notes');
            $table->string('ai_prompt_version', 50)->nullable()->after('ai_model_used');
            $table->timestamp('ai_last_checked_at')->nullable()->after('ai_prompt_version');
        });
    }

    public function down(): void
    {
        Schema::table('outreach_drafts', function (Blueprint $table) {
            $table->dropColumn([
                'ai_quality_score',
                'ai_personalization_score',
                'ai_relevance_score',
                'ai_proof_score',
                'ai_spam_risk_score',
                'ai_quality_notes',
                'ai_improvement_notes',
                'ai_model_used',
                'ai_prompt_version',
                'ai_last_checked_at',
            ]);
        });
    }
};
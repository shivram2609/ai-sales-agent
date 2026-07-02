<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('outreach_drafts', function (Blueprint $table) {
            // Shared verdict field, written by BOTH the rule-based checker
            // (Services\Drafts\DraftQualityService) and the AI checker
            // (Services\AI\DraftQualityService), so downstream views/queries
            // do not need to know which checker produced the result.
            $table->string('ai_verdict', 30)->nullable()->after('ai_spam_risk_score');
        });
    }

    public function down(): void
    {
        Schema::table('outreach_drafts', function (Blueprint $table) {
            $table->dropColumn('ai_verdict');
        });
    }
};

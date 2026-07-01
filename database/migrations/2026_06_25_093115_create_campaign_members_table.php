<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('campaign_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('prospect_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('prospect_contact_id')
                ->nullable()
                ->constrained('prospect_contacts')
                ->nullOnDelete();

            $table->foreignId('outreach_draft_id')
                ->nullable()
                ->constrained('outreach_drafts')
                ->nullOnDelete();

            $table->string('status', 80)->default('not_started');

            $table->timestamp('first_touch_at')->nullable();
            $table->timestamp('follow_up_1_due_at')->nullable();
            $table->timestamp('follow_up_1_sent_at')->nullable();
            $table->timestamp('follow_up_2_due_at')->nullable();
            $table->timestamp('follow_up_2_sent_at')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamp('last_interaction_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->unique(['campaign_id', 'prospect_contact_id'], 'campaign_contact_unique');
            $table->index(['campaign_id', 'status']);
            $table->index(['prospect_id', 'status']);
            $table->index('follow_up_1_due_at');
            $table->index('follow_up_2_due_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_members');
    }
};
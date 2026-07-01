<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outbound_email_jobs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('campaign_member_id')
                ->constrained()
                ->cascadeOnDelete();

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

            $table->string('email_type', 50);
            $table->string('sequence_mode', 50)->nullable();

            $table->string('to_email', 190);
            $table->string('to_name', 190)->nullable();

            $table->string('subject', 255);
            $table->string('preheader', 255)->nullable();
            $table->longText('body');

            $table->string('status', 50)->default('queued');

            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            $table->string('provider', 80)->nullable();
            $table->string('provider_message_id', 190)->nullable();
            $table->text('error_message')->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['status', 'scheduled_at']);
            $table->index(['campaign_member_id', 'email_type']);
            $table->index(['prospect_contact_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbound_email_jobs');
    }
};
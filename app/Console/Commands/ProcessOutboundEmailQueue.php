<?php

namespace App\Console\Commands;

use App\Models\CampaignMember;
use App\Models\OutboundEmailJob;
use App\Services\Email\BrevoEmailService;
use App\Services\Sending\ControlledSendingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessOutboundEmailQueue extends Command
{
    protected $signature = 'sending:process-queue';

    protected $description = 'Send due, queued outbound emails and schedule the next step in the sequence.';

    public function handle(ControlledSendingService $sendingService, BrevoEmailService $brevo): int
    {
        $limit = (int) config('sending.max_per_run', 25);

        $jobs = OutboundEmailJob::query()
            ->where('status', OutboundEmailJob::STATUS_QUEUED)
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->get();

        if ($jobs->isEmpty()) {
            $this->info('No due emails to send.');
            return self::SUCCESS;
        }

        foreach ($jobs as $job) {
            $this->processJob($job, $sendingService, $brevo);
        }

        return self::SUCCESS;
    }

    private function processJob(OutboundEmailJob $job, ControlledSendingService $sendingService, BrevoEmailService $brevo): void
    {
        // Re-check status under a lock in case something else (e.g. a manual
        // "cancel" click) touched this job between the query above and now.
        $locked = DB::transaction(function () use ($job) {
            $fresh = OutboundEmailJob::query()->lockForUpdate()->find($job->id);

            if (! $fresh || $fresh->status !== OutboundEmailJob::STATUS_QUEUED) {
                return null;
            }

            $fresh->update(['status' => OutboundEmailJob::STATUS_SENDING]);
            return $fresh;
        });

        if (! $locked) {
            return;
        }

        $member = CampaignMember::find($locked->campaign_member_id);

        // If the member was paused or stopped after this job was queued,
        // do not send. Put it back to queued so it can resume later, unless
        // the sequence was stopped entirely (stop() already cancels queued
        // jobs, so this mainly guards the "paused" case).
        if ($member && $member->status === CampaignMember::STATUS_PAUSED) {
            $locked->update(['status' => OutboundEmailJob::STATUS_QUEUED]);
            return;
        }

        try {
            $htmlContent = nl2br(e($locked->body));

            $result = $brevo->sendTransactionalEmail(
                toEmail: $locked->to_email,
                subject: $locked->subject ?: '(no subject)',
                htmlContent: $htmlContent,
                textContent: $locked->body,
                tags: ['ai-sales-agent', $locked->email_type],
                toName: $locked->to_name
            );

            $locked->update([
                'status' => OutboundEmailJob::STATUS_SENT,
                'sent_at' => now(),
                'provider' => 'brevo',
                'provider_message_id' => $result['messageId'] ?? null,
            ]);

            if ($member) {
                $this->advanceSequence($member, $locked, $sendingService);
            }

            $this->info("Sent job #{$locked->id} ({$locked->email_type}) to {$locked->to_email}");
        } catch (Throwable $e) {
            $locked->update([
                'status' => OutboundEmailJob::STATUS_FAILED,
                'failed_at' => now(),
                'error_message' => substr($e->getMessage(), 0, 500),
            ]);

            Log::error('Outbound email send failed', [
                'job_id' => $locked->id,
                'error' => $e->getMessage(),
            ]);

            $this->error("Failed job #{$locked->id}: {$e->getMessage()}");
        }
    }

    private function advanceSequence(CampaignMember $member, OutboundEmailJob $sentJob, ControlledSendingService $sendingService): void
    {
        // Manual-only members should never have automated jobs, but guard
        // anyway - never auto-queue a next step for manual mode.
        if ($member->sequence_mode === CampaignMember::MODE_MANUAL_ONLY) {
            return;
        }

        $nextType = match ($sentJob->email_type) {
            OutboundEmailJob::TYPE_FIRST_TOUCH => OutboundEmailJob::TYPE_FOLLOW_UP_1,
            OutboundEmailJob::TYPE_FOLLOW_UP_1 => OutboundEmailJob::TYPE_FOLLOW_UP_2,
            default => null,
        };

        $memberStatus = match ($sentJob->email_type) {
            OutboundEmailJob::TYPE_FIRST_TOUCH => CampaignMember::STATUS_SENT_AUTOMATICALLY,
            OutboundEmailJob::TYPE_FOLLOW_UP_1 => CampaignMember::STATUS_FOLLOW_UP_1_SENT,
            OutboundEmailJob::TYPE_FOLLOW_UP_2 => CampaignMember::STATUS_FOLLOW_UP_2_SENT,
            default => $member->status,
        };

        // "first_email_only" mode stops after the first touch - no
        // follow-ups get queued even though the mode is not manual.
        $shouldQueueNext = $nextType
            && $member->sequence_mode === CampaignMember::MODE_FULL_SEQUENCE;

        $nextJob = null;

        if ($shouldQueueNext) {
            $days = $nextType === OutboundEmailJob::TYPE_FOLLOW_UP_1
                ? config('sending.follow_up_1_days', 3)
                : config('sending.follow_up_2_days', 6);

            $nextJob = $sendingService->queueFollowUp($member, $nextType, now()->addDays($days));
        }

        $member->update([
            'status' => $nextJob ? CampaignMember::STATUS_SCHEDULED : $memberStatus,
            'last_interaction_at' => now(),
        ]);
    }
}

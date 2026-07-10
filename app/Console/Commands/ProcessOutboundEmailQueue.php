<?php

namespace App\Console\Commands;

use App\Models\CampaignMember;
use App\Models\OutboundEmailJob;
use App\Services\Email\BrevoEmailService;
use App\Services\Email\SenderRotationService;
use App\Services\Sending\ControlledSendingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessOutboundEmailQueue extends Command
{
    private const NEXT_ALLOWED_CACHE_KEY = 'sending:next_allowed_at';

    protected $signature = 'sending:process-queue';

    protected $description = 'Send at most one due, queued outbound email per run, respecting a randomized gap between sends, then schedule the next sequence step.';

    public function handle(ControlledSendingService $sendingService, BrevoEmailService $brevo, SenderRotationService $rotator): int
    {
        $nextAllowedAt = Cache::get(self::NEXT_ALLOWED_CACHE_KEY);

        if ($nextAllowedAt && now()->lt(\Carbon\Carbon::parse($nextAllowedAt))) {
            $this->info("Waiting until {$nextAllowedAt} before the next send. Skipping this run.");
            return self::SUCCESS;
        }

        // Only ever take the SINGLE earliest due job per run, never a batch.
        // This is what enforces the gap - even if 20 emails are due at once,
        // only one goes out per run, and the next run won't send again until
        // the gap has passed.
        $job = OutboundEmailJob::query()
            ->where('status', OutboundEmailJob::STATUS_QUEUED)
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->first();

        if (! $job) {
            $this->info('No due emails to send.');
            return self::SUCCESS;
        }

        $sent = $this->processJob($job, $sendingService, $brevo, $rotator);

        if ($sent) {
            $min = (int) config('sending.min_gap_seconds', 120);
            $max = (int) config('sending.max_gap_seconds', 180);
            $gapSeconds = random_int(min($min, $max), max($min, $max));
            Cache::forever(self::NEXT_ALLOWED_CACHE_KEY, now()->addSeconds($gapSeconds)->toDateTimeString());
        }

        return self::SUCCESS;
    }

    private function processJob(OutboundEmailJob $job, ControlledSendingService $sendingService, BrevoEmailService $brevo, SenderRotationService $rotator): bool
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
            return false;
        }

        $member = CampaignMember::find($locked->campaign_member_id);

        // If the member was paused or stopped after this job was queued,
        // do not send. Put it back to queued so it can resume later, unless
        // the sequence was stopped entirely (stop() already cancels queued
        // jobs, so this mainly guards the "paused" case).
        if ($member && $member->status === CampaignMember::STATUS_PAUSED) {
            $locked->update(['status' => OutboundEmailJob::STATUS_QUEUED]);
            return false;
        }

        $sender = $rotator->next();

        try {
            $htmlContent = nl2br(e($locked->body));

            $result = $brevo->sendTransactionalEmail(
                toEmail: $locked->to_email,
                subject: $locked->subject ?: '(no subject)',
                htmlContent: $htmlContent,
                textContent: $locked->body,
                tags: ['ai-sales-agent', $locked->email_type],
                toName: $locked->to_name,
                fromEmail: $sender['email'] ?? null,
                fromName: $sender['name'] ?? null
            );

            $locked->update([
                'status' => OutboundEmailJob::STATUS_SENT,
                'sent_at' => now(),
                'provider' => 'brevo',
                'provider_message_id' => $result['messageId'] ?? null,
                'meta' => array_merge($locked->meta ?? [], [
                    'sent_from_email' => $sender['email'] ?? null,
                    'sent_from_name' => $sender['name'] ?? null,
                ]),
            ]);

            if ($member) {
                $this->advanceSequence($member, $locked, $sendingService);
            }

            $this->info("Sent job #{$locked->id} ({$locked->email_type}) to {$locked->to_email} from {$sender['email']}");

            return true;
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

            // Still counts as an attempt for gap-timing purposes, so a
            // failing job can't be retried instantly in a tight loop.
            return true;
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

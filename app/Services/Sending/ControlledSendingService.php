<?php

namespace App\Services\Sending;

use App\Models\CampaignMember;
use App\Models\OutboundEmailJob;
use App\Models\ProspectContact;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ControlledSendingService
{
    public function approveAndQueue(
        CampaignMember $member,
        string $sequenceMode,
        ?string $scheduledAt = null,
        ?string $approvalNotes = null
    ): CampaignMember {
        return DB::transaction(function () use ($member, $sequenceMode, $scheduledAt, $approvalNotes) {
            $member->loadMissing(['campaign', 'prospect', 'contact', 'draft']);

            $this->guardCanApprove($member, $sequenceMode);

            $scheduledAtValue = $scheduledAt ? now()->parse($scheduledAt) : now();

            $member->update([
                'sequence_mode' => $sequenceMode,
                'approved_for_sending_at' => now(),
                'sequence_paused_at' => null,
                'sequence_stopped_at' => null,
                'stop_reason' => null,
                'approval_notes' => $this->clean($approvalNotes),
                'status' => $sequenceMode === CampaignMember::MODE_MANUAL_ONLY
                    ? CampaignMember::STATUS_APPROVED
                    : CampaignMember::STATUS_SCHEDULED,
            ]);

            if ($sequenceMode !== CampaignMember::MODE_MANUAL_ONLY) {
                $this->queueFirstTouch($member->fresh(['campaign', 'prospect', 'contact', 'draft']), $scheduledAtValue);
            }

            return $member->fresh(['campaign', 'prospect', 'contact', 'draft']);
        });
    }

    public function queueFirstTouch(CampaignMember $member, $scheduledAt): OutboundEmailJob
    {
        $member->loadMissing(['campaign', 'prospect', 'contact', 'draft']);

        if ($this->hasActiveJob($member, OutboundEmailJob::TYPE_FIRST_TOUCH)) {
            throw new RuntimeException('First email is already queued or in progress for this campaign member.');
        }

        $draft = $member->draft;
        $contact = $member->contact;

        if (! $draft) {
            throw new RuntimeException('Cannot queue email because no draft is linked.');
        }

        if (! $contact || ! $contact->email) {
            throw new RuntimeException('Cannot queue email because contact email is missing.');
        }

        return OutboundEmailJob::create([
            'campaign_member_id' => $member->id,
            'campaign_id' => $member->campaign_id,
            'prospect_id' => $member->prospect_id,
            'prospect_contact_id' => $member->prospect_contact_id,
            'outreach_draft_id' => $member->outreach_draft_id,
            'email_type' => OutboundEmailJob::TYPE_FIRST_TOUCH,
            'sequence_mode' => $member->sequence_mode,
            'to_email' => strtolower($contact->email),
            'to_name' => $contact->name,
            'subject' => $draft->subject,
            'preheader' => $draft->preheader,
            'body' => $draft->body,
            'status' => OutboundEmailJob::STATUS_QUEUED,
            'scheduled_at' => $scheduledAt,
            'meta' => [
                'created_by' => 'controlled_sending_v1',
                'approval_notes' => $member->approval_notes,
            ],
        ]);
    }

    public function cancelJob(OutboundEmailJob $job, ?string $reason = null): OutboundEmailJob
    {
        if (! in_array($job->status, [
            OutboundEmailJob::STATUS_QUEUED,
            OutboundEmailJob::STATUS_FAILED,
        ], true)) {
            throw new RuntimeException('Only queued or failed jobs can be cancelled.');
        }

        $job->update([
            'status' => OutboundEmailJob::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'error_message' => $this->clean($reason) ?: 'Cancelled manually.',
        ]);

        return $job->fresh();
    }

    public function pause(CampaignMember $member): CampaignMember
    {
        if ($this->isClosed($member)) {
            throw new RuntimeException('Closed campaign members cannot be paused.');
        }

        $member->update([
            'status' => CampaignMember::STATUS_PAUSED,
            'sequence_paused_at' => now(),
            'last_interaction_at' => now(),
        ]);

        return $member->fresh();
    }

    public function resume(CampaignMember $member): CampaignMember
    {
        if ($member->sequence_stopped_at) {
            throw new RuntimeException('Stopped sequence cannot be resumed.');
        }

        if ($member->status !== CampaignMember::STATUS_PAUSED) {
            throw new RuntimeException('Only paused campaign members can be resumed.');
        }

        $member->update([
            'status' => $member->outboundEmailJobs()
                ->where('status', OutboundEmailJob::STATUS_QUEUED)
                ->exists()
                    ? CampaignMember::STATUS_SCHEDULED
                    : CampaignMember::STATUS_APPROVED,
            'sequence_paused_at' => null,
            'last_interaction_at' => now(),
        ]);

        return $member->fresh();
    }

    public function stop(CampaignMember $member, ?string $reason = null): CampaignMember
    {
        DB::transaction(function () use ($member, $reason) {
            $member->outboundEmailJobs()
                ->where('status', OutboundEmailJob::STATUS_QUEUED)
                ->update([
                    'status' => OutboundEmailJob::STATUS_CANCELLED,
                    'cancelled_at' => now(),
                    'error_message' => 'Cancelled because sequence was stopped.',
                ]);

            $member->update([
                'status' => CampaignMember::STATUS_SEQUENCE_STOPPED,
                'sequence_stopped_at' => now(),
                'stop_reason' => $this->clean($reason) ?: 'Stopped manually.',
                'last_interaction_at' => now(),
            ]);
        });

        return $member->fresh();
    }

    private function guardCanApprove(CampaignMember $member, string $sequenceMode): void
    {
        if (! array_key_exists($sequenceMode, CampaignMember::sequenceModes())) {
            throw new RuntimeException('Invalid sequence mode.');
        }

        if ($this->isClosed($member)) {
            throw new RuntimeException('This campaign member is closed and cannot be approved for sending.');
        }

        if (! $member->contact) {
            throw new RuntimeException('No contact is linked.');
        }

        if ($member->contact->status !== ProspectContact::STATUS_ACTIVE) {
            throw new RuntimeException('Only active contacts can be approved for sending.');
        }

        if (! $member->contact->email) {
            throw new RuntimeException('Contact email is missing.');
        }

        if ($sequenceMode !== CampaignMember::MODE_MANUAL_ONLY && ! $member->draft) {
            throw new RuntimeException('A draft is required before automated sending can be queued.');
        }

        if ($sequenceMode !== CampaignMember::MODE_MANUAL_ONLY && empty($member->draft?->subject)) {
            throw new RuntimeException('Draft subject is missing.');
        }

        if ($sequenceMode !== CampaignMember::MODE_MANUAL_ONLY && empty($member->draft?->body)) {
            throw new RuntimeException('Draft body is missing.');
        }
    }

    private function hasActiveJob(CampaignMember $member, string $emailType): bool
    {
        return OutboundEmailJob::query()
            ->where('campaign_member_id', $member->id)
            ->where('email_type', $emailType)
            ->whereIn('status', [
                OutboundEmailJob::STATUS_QUEUED,
                OutboundEmailJob::STATUS_SENDING,
            ])
            ->exists();
    }

    private function isClosed(CampaignMember $member): bool
    {
        return in_array($member->status, [
            CampaignMember::STATUS_REPLIED,
            CampaignMember::STATUS_NOT_INTERESTED,
            CampaignMember::STATUS_BOUNCED,
            CampaignMember::STATUS_PAUSED,
            CampaignMember::STATUS_SEQUENCE_STOPPED,
        ], true);
    }

    private function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutboundEmailJob extends Model
{
    public const TYPE_FIRST_TOUCH = 'first_touch';
    public const TYPE_FOLLOW_UP_1 = 'follow_up_1';
    public const TYPE_FOLLOW_UP_2 = 'follow_up_2';

    public const STATUS_QUEUED = 'queued';
    public const STATUS_SENDING = 'sending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_SKIPPED = 'skipped';

    protected $fillable = [
        'campaign_member_id',
        'campaign_id',
        'prospect_id',
        'prospect_contact_id',
        'outreach_draft_id',
        'email_type',
        'sequence_mode',
        'to_email',
        'to_name',
        'subject',
        'preheader',
        'body',
        'status',
        'scheduled_at',
        'sent_at',
        'cancelled_at',
        'failed_at',
        'provider',
        'provider_message_id',
        'error_message',
        'meta',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'failed_at' => 'datetime',
        'meta' => 'array',
    ];

    public function campaignMember()
    {
        return $this->belongsTo(CampaignMember::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }

    public function contact()
    {
        return $this->belongsTo(ProspectContact::class, 'prospect_contact_id');
    }

    public function draft()
    {
        return $this->belongsTo(OutreachDraft::class, 'outreach_draft_id');
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_QUEUED => 'Queued',
            self::STATUS_SENDING => 'Sending',
            self::STATUS_SENT => 'Sent',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_SKIPPED => 'Skipped',
        ];
    }

    public static function types(): array
    {
        return [
            self::TYPE_FIRST_TOUCH => 'First Email',
            self::TYPE_FOLLOW_UP_1 => 'Follow-up 1',
            self::TYPE_FOLLOW_UP_2 => 'Follow-up 2',
        ];
    }
}
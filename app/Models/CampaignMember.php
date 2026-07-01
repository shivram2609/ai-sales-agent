<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignMember extends Model
{
    public const STATUS_NOT_STARTED = 'not_started';
    public const STATUS_DRAFT_READY = 'draft_ready';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_SENT_MANUALLY = 'sent_manually';
    public const STATUS_FOLLOW_UP_1_DUE = 'follow_up_1_due';
    public const STATUS_FOLLOW_UP_1_SENT = 'follow_up_1_sent';
    public const STATUS_FOLLOW_UP_2_DUE = 'follow_up_2_due';
    public const STATUS_FOLLOW_UP_2_SENT = 'follow_up_2_sent';
    public const STATUS_REPLIED = 'replied';
    public const STATUS_NOT_INTERESTED = 'not_interested';
    public const STATUS_BOUNCED = 'bounced';
    public const STATUS_PAUSED = 'paused';
	
	public const STATUS_SCHEDULED = 'scheduled';
	public const STATUS_SENT_AUTOMATICALLY = 'sent_automatically';
	public const STATUS_SEQUENCE_STOPPED = 'sequence_stopped';

	public const MODE_MANUAL_ONLY = 'manual_only';
	public const MODE_FIRST_EMAIL_ONLY = 'first_email_only';
	public const MODE_FULL_SEQUENCE = 'full_sequence';

    protected $fillable = [
        'campaign_id',
        'prospect_id',
        'prospect_contact_id',
        'outreach_draft_id',
        'status',
        'first_touch_at',
        'follow_up_1_due_at',
        'follow_up_1_sent_at',
        'follow_up_2_due_at',
        'follow_up_2_sent_at',
        'replied_at',
        'last_interaction_at',
        'notes',
		'sequence_mode',
		'approved_for_sending_at',
		'sequence_paused_at',
		'sequence_stopped_at',
		'approval_notes',
		'stop_reason',
    ];

    protected $casts = [
        'first_touch_at' => 'datetime',
        'follow_up_1_due_at' => 'datetime',
        'follow_up_1_sent_at' => 'datetime',
        'follow_up_2_due_at' => 'datetime',
        'follow_up_2_sent_at' => 'datetime',
        'replied_at' => 'datetime',
        'last_interaction_at' => 'datetime',
		'approved_for_sending_at' => 'datetime',
		'sequence_paused_at' => 'datetime',
		'sequence_stopped_at' => 'datetime',
    ];

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
            self::STATUS_NOT_STARTED => 'Not Started',
            self::STATUS_DRAFT_READY => 'Draft Ready',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_SENT_MANUALLY => 'Sent Manually',
            self::STATUS_FOLLOW_UP_1_DUE => 'Follow-up 1 Due',
            self::STATUS_FOLLOW_UP_1_SENT => 'Follow-up 1 Sent',
            self::STATUS_FOLLOW_UP_2_DUE => 'Follow-up 2 Due',
            self::STATUS_FOLLOW_UP_2_SENT => 'Follow-up 2 Sent',
            self::STATUS_REPLIED => 'Replied',
            self::STATUS_NOT_INTERESTED => 'Not Interested',
            self::STATUS_BOUNCED => 'Bounced',
            self::STATUS_PAUSED => 'Paused',
			self::STATUS_SCHEDULED => 'Scheduled',
			self::STATUS_SENT_AUTOMATICALLY => 'Sent Automatically',
			self::STATUS_SEQUENCE_STOPPED => 'Sequence Stopped',
        ];
    }
	
	public function outboundEmailJobs()
	{
		return $this->hasMany(\App\Models\OutboundEmailJob::class);
	}
	
	public static function sequenceModes(): array
	{
		return [
			self::MODE_MANUAL_ONLY => 'Manual Only',
			self::MODE_FIRST_EMAIL_ONLY => 'Send First Email Only',
			self::MODE_FULL_SEQUENCE => 'Full Sequence',
		];
	}
}
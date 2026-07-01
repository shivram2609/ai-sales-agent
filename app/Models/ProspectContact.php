<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProspectContact extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_NEEDS_RESEARCH = 'needs_research';
    public const STATUS_WRONG_PERSON = 'wrong_person';
    public const STATUS_BOUNCED = 'bounced';
    public const STATUS_DO_NOT_CONTACT = 'do_not_contact';

    protected $fillable = [
        'prospect_id',
        'name',
        'email',
        'title',
        'linkedin_url',
        'phone',
        'source',
        'status',
        'is_primary',
        'notes',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_NEEDS_RESEARCH => 'Needs Research',
            self::STATUS_WRONG_PERSON => 'Wrong Person',
            self::STATUS_BOUNCED => 'Bounced',
            self::STATUS_DO_NOT_CONTACT => 'Do Not Contact',
        ];
    }

    public static function sources(): array
    {
        return [
            'manual' => 'Manual',
            'website' => 'Website',
            'linkedin' => 'LinkedIn',
            'hunter' => 'Hunter',
            'apollo' => 'Apollo',
            'other' => 'Other',
        ];
    }
	
	public function drafts()
	{
		return $this->hasMany(\App\Models\OutreachDraft::class, 'prospect_contact_id');
	}
	
	public function campaignMembers()
	{
		return $this->hasMany(\App\Models\CampaignMember::class, 'prospect_contact_id');
	}
	
	public function outboundEmailJobs()
	{
		return $this->hasMany(\App\Models\OutboundEmailJob::class, 'prospect_contact_id');
	}
	
}
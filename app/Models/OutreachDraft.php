<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OutreachDraft extends Model
{
    protected $fillable = [
        'prospect_id',
        'subject',
        'preheader',
        'body',
        'linkedin_connection_note',
        'linkedin_followup',
        'follow_up_1',
        'follow_up_2',
        'status',

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
		'prospect_contact_id',
    ];

    protected $casts = [
        'ai_last_checked_at' => 'datetime',
    ];

    public function prospect()
    {
        return $this->belongsTo(Prospect::class);
    }

    public function aiLogs()
    {
        return $this->morphMany(\App\Models\AiLog::class, 'loggable');
    }
	
	public function contact()
	{
		return $this->belongsTo(\App\Models\ProspectContact::class, 'prospect_contact_id');
	}
	
	public function campaignMembers()
	{
		return $this->hasMany(\App\Models\CampaignMember::class, 'outreach_draft_id');
	}
	
	public function outboundEmailJobs()
	{
		return $this->hasMany(\App\Models\OutboundEmailJob::class, 'outreach_draft_id');
	}
}
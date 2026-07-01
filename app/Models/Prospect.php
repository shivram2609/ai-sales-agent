<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prospect extends Model
{
    protected $fillable = [
        'website_url', 'domain', 'company_name', 'category_guess', 'country_guess',
        'source', 'notes', 'status', 'fit_score',
    ];

    public function pages(): HasMany { return $this->hasMany(ProspectPage::class); }
    public function analyses(): HasMany { return $this->hasMany(ProspectAnalysis::class); }
    public function drafts(): HasMany { return $this->hasMany(OutreachDraft::class); }
    public function reviewActions(): HasMany { return $this->hasMany(ReviewAction::class); }
    public function agentLogs(): HasMany { return $this->hasMany(AgentLog::class); }
    public function discoveryResults(): HasMany { return $this->hasMany(DiscoveryResult::class); }
	
	public function contacts()
	{
		return $this->hasMany(\App\Models\ProspectContact::class)
			->orderByDesc('is_primary')
			->latest();
	}

	public function primaryContact()
	{
		return $this->hasOne(\App\Models\ProspectContact::class)
			->where('is_primary', true);
	}
	
	public function campaignMembers()
	{
		return $this->hasMany(\App\Models\CampaignMember::class);
	}
}

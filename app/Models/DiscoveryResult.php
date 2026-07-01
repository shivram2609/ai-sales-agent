<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscoveryResult extends Model
{
    protected $fillable = [
		'discovery_campaign_id',
		'source_query',
		'result_title',
		'result_url',
		'normalized_domain',
		'snippet',
		'source_engine',
		'rank',
		'company_name_guess',
		'category_guess',
		'region_guess',
		'relevance_score',
		'filter_status',
		'reason',
		'raw',
		'converted_to_prospect',
		'prospect_id',
	];

	protected $casts = [
		'raw' => 'array',
		'converted_to_prospect' => 'boolean',
	];

    public function campaign(): BelongsTo { return $this->belongsTo(DiscoveryCampaign::class, 'discovery_campaign_id'); }
    public function prospect(): BelongsTo { return $this->belongsTo(Prospect::class); }
}

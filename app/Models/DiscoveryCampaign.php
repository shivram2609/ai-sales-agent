<?php

namespace App\Models;

use App\Models\DiscoveryResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscoveryCampaign extends Model
{
    protected $fillable = [
		'name',
		'service_focus',
		'target_audience',
		'regions_json',
		'cities_json',
		'exclude_terms_json',
		'exact_queries_json',
		'query_mode',
		'max_results_per_query',
		'source_engine',
		'status',
		'notes',
	];

    protected $casts = [
		'service_focus' => 'array',
		'target_audience' => 'array',
		'regions_json' => 'array',
		'cities_json' => 'array',
		'exclude_terms_json' => 'array',
		'exact_queries_json' => 'array',
	];

    public function results(): HasMany { return $this->hasMany(DiscoveryResult::class); }
    public function logs(): HasMany { return $this->hasMany(DiscoveryLog::class); }
	
	//return $this->hasMany(DiscoveryResult::class);
}

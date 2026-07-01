<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscoveryLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['discovery_campaign_id', 'step_name', 'input', 'output', 'error', 'created_at'];

    protected $casts = [
        'input' => 'array',
        'output' => 'array',
        'created_at' => 'datetime',
    ];

    public function campaign(): BelongsTo { return $this->belongsTo(DiscoveryCampaign::class, 'discovery_campaign_id'); }
}

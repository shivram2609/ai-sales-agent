<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProspectAnalysis extends Model
{
    protected $fillable = [
        'prospect_id', 'company_type', 'primary_services_json', 'target_clients_json',
        'visible_technical_depth', 'likely_gap', 'partnership_angle', 'region', 'confidence',
        'rule_score', 'ai_score', 'final_score', 'recommended_action', 'reasoning_summary', 'raw',
    ];

    protected $casts = [
        'primary_services_json' => 'array',
        'target_clients_json' => 'array',
        'raw' => 'array',
    ];

    public function prospect(): BelongsTo { return $this->belongsTo(Prospect::class); }
}

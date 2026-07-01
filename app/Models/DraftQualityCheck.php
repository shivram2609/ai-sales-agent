<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DraftQualityCheck extends Model
{
    protected $fillable = [
        'outreach_draft_id', 'has_fake_personalization', 'has_misleading_claim', 'too_salesy',
        'too_long', 'missing_opt_out', 'too_many_links', 'risk_level', 'fixes_required',
    ];

    protected $casts = [
        'has_fake_personalization' => 'boolean',
        'has_misleading_claim' => 'boolean',
        'too_salesy' => 'boolean',
        'too_long' => 'boolean',
        'missing_opt_out' => 'boolean',
        'too_many_links' => 'boolean',
        'fixes_required' => 'array',
    ];

    public function outreachDraft(): BelongsTo { return $this->belongsTo(OutreachDraft::class); }
}

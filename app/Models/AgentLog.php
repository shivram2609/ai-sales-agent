<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentLog extends Model
{
    protected $fillable = ['prospect_id', 'step_name', 'input', 'output', 'error'];

    protected $casts = [
        'input' => 'array',
        'output' => 'array',
    ];

    public function prospect(): BelongsTo { return $this->belongsTo(Prospect::class); }
}

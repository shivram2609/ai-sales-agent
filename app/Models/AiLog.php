<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiLog extends Model
{
    protected $fillable = [
        'loggable_type',
        'loggable_id',
        'action',
        'model',
        'prompt_version',
        'input_snapshot',
        'output_json',
        'status',
        'error_message',
    ];

    protected $casts = [
        'input_snapshot' => 'array',
        'output_json' => 'array',
    ];

    public function loggable()
    {
        return $this->morphTo();
    }
}
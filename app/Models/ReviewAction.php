<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewAction extends Model
{
    protected $fillable = ['prospect_id', 'action', 'notes'];
    public function prospect(): BelongsTo { return $this->belongsTo(Prospect::class); }
}

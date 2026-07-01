<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProspectPage extends Model
{
    protected $fillable = [
        'prospect_id', 'page_type', 'url', 'title', 'meta_description', 'headings_json',
        'links_json', 'emails_json', 'main_text', 'raw_html_snapshot', 'crawl_status', 'error_message',
    ];

    protected $casts = [
        'headings_json' => 'array',
        'links_json' => 'array',
        'emails_json' => 'array',
    ];

    public function prospect(): BelongsTo { return $this->belongsTo(Prospect::class); }
}

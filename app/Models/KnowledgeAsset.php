<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KnowledgeAsset extends Model
{
    protected $fillable = [
		'title',
		'asset_type',
		'url',
		'summary',
		'tags_json',
		'industries_json',
		'technologies_json',
		'use_when',
		'avoid_when',
		'content_snippet',
		'is_active',
	];

	protected $casts = [
		'tags_json' => 'array',
		'industries_json' => 'array',
		'technologies_json' => 'array',
		'is_active' => 'boolean',
	];
}

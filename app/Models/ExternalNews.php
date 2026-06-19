<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ExternalNews extends Model
{
    use HasUuids;

    protected $table = 'external_news_cache';

    protected $fillable = [
        'title',
        'content',
        'image',
        'source',
        'url',
        'category',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];
}

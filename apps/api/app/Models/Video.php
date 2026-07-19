<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasTranslations;

    protected $fillable = [
        'slug',
        'url',
        'thumbnail',
        'video_type',
        'youtube_id',
        'duration',
        'views_count',
        'likes_count',
        'published_at',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function comments()
    {
        return $this->hasMany(VideoComment::class);
    }
}

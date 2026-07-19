<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasTranslations;

    protected $fillable = [
        'slug',
        'image',
        'category',
        'published_at',
        'is_published',
        'views_count',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];
}

<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class GreenCampusArticle extends Model
{
    use HasTranslations;

    protected $fillable = [
        'slug',
        'category',
        'image',
        'gallery',
        'views',
        'published_at',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'gallery' => 'array',
        'published_at' => 'datetime',
        'is_published' => 'boolean',
    ];
}

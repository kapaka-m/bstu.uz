<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasTranslations;

    protected $fillable = ['slug', 'type', 'priority', 'image', 'starts_at', 'ends_at', 'is_published', 'views_count'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_published' => 'boolean',
        'views_count' => 'integer',
    ];
}

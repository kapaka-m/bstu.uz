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
        'publisher_id',
        'published_at',
        'is_published',
        'views_count',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function publisher()
    {
        return $this->belongsTo(BlogDepartment::class, 'publisher_id');
    }
}

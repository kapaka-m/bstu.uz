<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasTranslations;

    protected $fillable = [
        'slug',
        'image',
        'author',
        'author_image',
        'blog_department_id',
        'category',
        'published_at',
        'is_published',
        'views_count',
        'comments_count',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_published' => 'boolean',
    ];

    public function comments()
    {
        return $this->hasMany(BlogComment::class);
    }

    public function blogDepartment()
    {
        return $this->belongsTo(BlogDepartment::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogTranslation extends Model
{
    protected $fillable = [
        'blog_id',
        'locale',
        'title',
        'author',
        'category_label',
        'summary',
        'content',
        'meta_title',
        'meta_description',
    ];

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}

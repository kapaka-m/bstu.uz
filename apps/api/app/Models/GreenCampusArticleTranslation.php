<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GreenCampusArticleTranslation extends Model
{
    protected $fillable = ['green_campus_article_id', 'locale', 'title', 'category', 'excerpt', 'content', 'author'];

    public function article()
    {
        return $this->belongsTo(GreenCampusArticle::class, 'green_campus_article_id');
    }
}

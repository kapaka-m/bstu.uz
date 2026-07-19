<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsTranslation extends Model
{
    protected $fillable = ['news_id', 'locale', 'title', 'summary', 'content', 'meta_title', 'meta_description'];

    public function news()
    {
        return $this->belongsTo(News::class);
    }
}

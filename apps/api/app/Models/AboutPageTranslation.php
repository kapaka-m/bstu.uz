<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutPageTranslation extends Model
{
    protected $fillable = [
        'about_page_id',
        'locale',
        'content',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public function aboutPage()
    {
        return $this->belongsTo(AboutPage::class);
    }
}

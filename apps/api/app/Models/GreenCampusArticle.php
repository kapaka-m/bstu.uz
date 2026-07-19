<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class GreenCampusArticle extends Model
{
    use HasTranslations;

    protected $fillable = ['slug', 'image', 'gallery', 'views'];

    protected $casts = [
        'gallery' => 'array',
    ];
}

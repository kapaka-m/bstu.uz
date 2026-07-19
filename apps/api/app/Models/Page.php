<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasTranslations;

    protected $fillable = ['slug', 'template', 'is_published', 'sort_order'];

    public function blocks()
    {
        return $this->hasMany(PageBlock::class)->orderBy('sort_order');
    }
}

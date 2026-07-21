<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasTranslations;

    protected $fillable = [
        'slug',
        'icon',
        'url',
        'color',
        'home_visible',
        'opens_new_tab',
        'sort_order',
        'is_active',
    ];
}

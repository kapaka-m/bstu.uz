<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class AdministrationProfile extends Model
{
    use HasTranslations;

    protected $fillable = [
        'slug',
        'photo',
        'email',
        'phone',
        'telegram_url',
        'sort_order',
        'is_rector',
        'is_published',
    ];

    protected $casts = [
        'is_rector' => 'boolean',
        'is_published' => 'boolean',
        'sort_order' => 'integer',
    ];
}

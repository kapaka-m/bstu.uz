<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class GreenCampusSetting extends Model
{
    use HasTranslations;

    protected $fillable = ['key', 'home_limit', 'recent_limit', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

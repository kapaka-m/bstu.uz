<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsEventSetting extends Model
{
    protected $fillable = [
        'key',
        'home_limit',
        'recent_limit',
        'is_active',
    ];

    protected $casts = [
        'home_limit' => 'integer',
        'recent_limit' => 'integer',
        'is_active' => 'boolean',
    ];

    public function translations()
    {
        return $this->hasMany(NewsEventSettingTranslation::class);
    }
}

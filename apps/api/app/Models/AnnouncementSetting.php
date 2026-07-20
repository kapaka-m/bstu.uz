<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementSetting extends Model
{
    protected $fillable = [
        'key',
        'home_limit',
        'recent_limit',
        'important_limit',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function translations()
    {
        return $this->hasMany(AnnouncementSettingTranslation::class);
    }
}

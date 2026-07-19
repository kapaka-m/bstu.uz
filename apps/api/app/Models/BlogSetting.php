<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogSetting extends Model
{
    protected $fillable = [
        'key',
        'home_limit',
        'recent_limit',
        'home_icon',
        'tags',
        'is_active',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_active' => 'boolean',
    ];

    public function translations()
    {
        return $this->hasMany(BlogSettingTranslation::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdministrationSetting extends Model
{
    protected $fillable = ['key', 'home_limit', 'is_active'];

    protected $casts = [
        'home_limit' => 'integer',
        'is_active' => 'boolean',
    ];

    public function translations()
    {
        return $this->hasMany(AdministrationSettingTranslation::class);
    }
}

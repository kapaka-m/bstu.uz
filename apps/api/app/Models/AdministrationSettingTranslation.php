<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdministrationSettingTranslation extends Model
{
    protected $fillable = [
        'administration_setting_id',
        'locale',
        'home_tag',
        'home_title',
        'reception_label',
        'phone_label',
        'email_label',
        'telegram_label',
        'rector_bot_label',
        'structure_title',
    ];

    public function setting()
    {
        return $this->belongsTo(AdministrationSetting::class, 'administration_setting_id');
    }
}

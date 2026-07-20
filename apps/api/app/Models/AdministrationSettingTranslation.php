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
        'profile_category_label',
        'email_address_label',
        'phone_number_label',
        'office_hours_label',
        'academic_rank_label',
        'biography_label',
        'duties_label',
        'achievements_label',
    ];

    public function setting()
    {
        return $this->belongsTo(AdministrationSetting::class, 'administration_setting_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UniversityCenterSettingTranslation extends Model
{
    protected $fillable = [
        'center_setting_id',
        'locale',
        'sidebar_title',
        'structure_label',
        'about_label',
        'staff_label',
        'default_head_desc',
        'mission_label',
        'support_title',
        'support_desc',
        'contact_btn_label',
        'function_badge_label',
    ];

    public function setting()
    {
        return $this->belongsTo(UniversityCenterSetting::class, 'center_setting_id');
    }
}

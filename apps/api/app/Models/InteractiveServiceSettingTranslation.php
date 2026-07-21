<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InteractiveServiceSettingTranslation extends Model
{
    protected $fillable = [
        'interactive_service_setting_id',
        'locale',
        'home_tag',
        'home_title',
        'view_all_label',
        'loading_label',
        'no_results_label',
    ];

    public function setting()
    {
        return $this->belongsTo(InteractiveServiceSetting::class, 'interactive_service_setting_id');
    }
}

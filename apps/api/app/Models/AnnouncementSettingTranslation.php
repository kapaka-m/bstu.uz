<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementSettingTranslation extends Model
{
    protected $fillable = [
        'announcement_setting_id',
        'locale',
        'home_tag',
        'home_title',
        'view_all_label',
        'read_details_label',
        'search_title',
        'search_placeholder',
        'categories_title',
        'recent_title',
        'all_label',
        'views_label',
        'important_label',
        'loading_label',
        'no_results_label',
        'clear_filters_label',
        'share_label',
        'copy_link_label',
        'copied_label',
        'published_by_label',
        'publisher_name',
    ];

    public function setting()
    {
        return $this->belongsTo(AnnouncementSetting::class, 'announcement_setting_id');
    }
}

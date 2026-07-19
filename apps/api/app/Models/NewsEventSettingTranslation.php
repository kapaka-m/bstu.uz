<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsEventSettingTranslation extends Model
{
    protected $fillable = [
        'news_event_setting_id',
        'locale',
        'home_tag',
        'home_title',
        'home_subtitle',
        'view_all_label',
        'read_details_label',
        'search_title',
        'search_placeholder',
        'categories_title',
        'recent_title',
        'all_news_label',
        'news_label',
        'events_label',
        'views_label',
        'loading_label',
        'no_results_label',
        'clear_filters_label',
    ];

    public function setting()
    {
        return $this->belongsTo(NewsEventSetting::class, 'news_event_setting_id');
    }
}

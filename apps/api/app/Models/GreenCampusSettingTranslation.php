<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GreenCampusSettingTranslation extends Model
{
    protected $fillable = [
        'green_campus_setting_id',
        'locale',
        'home_tag',
        'home_title',
        'view_all_label',
        'read_more_label',
        'search_title',
        'search_placeholder',
        'categories_title',
        'recent_title',
        'all_label',
        'no_results_label',
        'callout_title',
        'callout_description',
        'callout_cta_label',
        'callout_email',
        'views_label',
        'gallery_label',
        'related_label',
        'close_viewer_label',
        'previous_image_label',
        'next_image_label',
        'category_labels',
    ];

    protected $casts = [
        'category_labels' => 'array',
    ];

    public function setting()
    {
        return $this->belongsTo(GreenCampusSetting::class, 'green_campus_setting_id');
    }
}

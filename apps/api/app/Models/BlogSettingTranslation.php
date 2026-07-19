<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogSettingTranslation extends Model
{
    protected $fillable = [
        'blog_setting_id',
        'locale',
        'home_tag',
        'home_title',
        'view_all_label',
        'read_more_label',
        'search_title',
        'search_placeholder',
        'categories_title',
        'recent_title',
        'tags_title',
        'all_blog_label',
        'loading_label',
        'no_results_label',
        'clear_filters_label',
        'back_to_blog_label',
        'comments_label',
        'reply_label',
        'form_title',
        'form_name_label',
        'form_email_label',
        'form_comment_label',
        'form_submit_label',
    ];

    public function setting()
    {
        return $this->belongsTo(BlogSetting::class, 'blog_setting_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoGallerySettingTranslation extends Model
{
    protected $fillable = [
        'video_gallery_setting_id',
        'locale',
        'home_tag',
        'home_title',
        'home_subtitle',
        'view_all_label',
        'recommended_label',
        'videos_label',
        'description_title',
        'show_more_label',
        'show_less_label',
        'like_label',
        'liked_label',
        'share_label',
        'subscribe_label',
        'subscribed_label',
        'subscribers_label',
        'views_label',
        'channel_name',
        'link_copied_label',
        'no_videos_label',
        'comments_label',
        'reply_label',
        'form_title',
        'form_comment_label',
        'form_submit_label',
        'sign_in_title',
        'sign_in_text',
        'sign_in_action',
        'signed_in_as_label',
        'category_label',
        'duration_label',
        'platform_label',
        'local_label',
        'youtube_label',
        'playing_label',
        'verified_channel_label',
        'category_labels',
    ];

    protected $casts = [
        'category_labels' => 'array',
    ];

    public function setting()
    {
        return $this->belongsTo(VideoGallerySetting::class, 'video_gallery_setting_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoGallerySetting extends Model
{
    protected $fillable = [
        'key',
        'home_limit',
        'subscriber_count',
        'youtube_channel_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function translations()
    {
        return $this->hasMany(VideoGallerySettingTranslation::class);
    }
}

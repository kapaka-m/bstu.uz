<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoTranslation extends Model
{
    protected $fillable = ['video_id', 'locale', 'title', 'category', 'description'];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}

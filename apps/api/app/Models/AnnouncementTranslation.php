<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementTranslation extends Model
{
    protected $fillable = ['announcement_id', 'locale', 'category_label', 'title', 'summary', 'content'];

    public function announcement()
    {
        return $this->belongsTo(Announcement::class);
    }
}

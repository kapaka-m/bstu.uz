<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AboutPageContentEntryTranslation extends Model
{
    protected $fillable = [
        'about_page_content_entry_id',
        'locale',
        'value',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(AboutPageContentEntry::class, 'about_page_content_entry_id');
    }
}

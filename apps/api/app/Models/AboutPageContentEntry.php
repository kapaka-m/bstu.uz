<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AboutPageContentEntry extends Model
{
    protected $fillable = [
        'about_page_id',
        'path',
        'value_type',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function aboutPage(): BelongsTo
    {
        return $this->belongsTo(AboutPage::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(AboutPageContentEntryTranslation::class, 'about_page_content_entry_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplyPageTranslation extends Model
{
    protected $fillable = [
        'apply_page_id',
        'locale',
        'content',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(ApplyPage::class, 'apply_page_id');
    }
}

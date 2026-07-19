<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class PageBlock extends Model
{
    use HasTranslations;

    protected $fillable = ['page_id', 'block_key', 'type', 'sort_order', 'settings_json', 'is_active'];

    protected $casts = [
        'settings_json' => 'array',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}

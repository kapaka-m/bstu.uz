<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class HomeSectionItem extends Model
{
    use HasTranslations;

    protected $fillable = [
        'home_section_id',
        'item_key',
        'icon',
        'value',
        'suffix',
        'url',
        'sort_order',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function section()
    {
        return $this->belongsTo(HomeSection::class);
    }
}

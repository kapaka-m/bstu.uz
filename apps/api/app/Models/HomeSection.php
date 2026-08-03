<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    use HasTranslations;

    protected $fillable = [
        'section_key',
        'section_type',
        'sort_order',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(HomeSectionItem::class)->orderBy('sort_order')->orderBy('id');
    }
}

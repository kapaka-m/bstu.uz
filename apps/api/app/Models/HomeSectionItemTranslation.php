<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSectionItemTranslation extends Model
{
    protected $fillable = [
        'home_section_item_id',
        'locale',
        'title',
        'description',
        'label',
        'action_label',
    ];

    public function item()
    {
        return $this->belongsTo(HomeSectionItem::class, 'home_section_item_id');
    }
}

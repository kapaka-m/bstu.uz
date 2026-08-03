<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSectionTranslation extends Model
{
    protected $fillable = [
        'home_section_id',
        'locale',
        'eyebrow',
        'title',
        'subtitle',
        'description',
        'secondary_title',
        'secondary_description',
        'cta_label',
        'cta_url',
        'image_alt',
    ];

    public function section()
    {
        return $this->belongsTo(HomeSection::class, 'home_section_id');
    }
}

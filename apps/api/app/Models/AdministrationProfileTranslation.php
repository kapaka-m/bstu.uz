<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdministrationProfileTranslation extends Model
{
    protected $fillable = [
        'administration_profile_id',
        'locale',
        'full_name',
        'position',
        'degree',
        'office_hours',
        'about',
        'details',
        'achievements',
    ];

    protected $casts = [
        'achievements' => 'array',
    ];

    public function administrationProfile()
    {
        return $this->belongsTo(AdministrationProfile::class);
    }
}

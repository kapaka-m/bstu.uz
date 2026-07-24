<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UniversityCenterTranslation extends Model
{
    protected $fillable = [
        'university_center_id',
        'locale',
        'name',
        'head',
        'head_title',
        'office_hours',
        'about',
        'functions',
    ];

    protected $casts = [
        'functions' => 'array',
    ];

    public function universityCenter()
    {
        return $this->belongsTo(UniversityCenter::class);
    }
}

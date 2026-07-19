<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class WebFooter extends Model
{
    use HasTranslations;

    protected $fillable = [
        'key',
        'useful_links',
        'faculty_links',
        'social_links',
        'admissions_apply_url',
        'phone',
        'email',
        'copyright_year',
        'is_active',
    ];

    protected $casts = [
        'useful_links' => 'array',
        'faculty_links' => 'array',
        'social_links' => 'array',
        'copyright_year' => 'integer',
        'is_active' => 'boolean',
    ];
}

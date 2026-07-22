<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class AboutPage extends Model
{
    use HasTranslations;

    protected $fillable = [
        'key',
        'hero_contact_url',
        'hero_campus_url',
        'identity_image',
        'rector_profile_slug',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];
}

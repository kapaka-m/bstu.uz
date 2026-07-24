<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class UniversityCenter extends Model
{
    use HasTranslations;

    protected $fillable = [
        'slug',
        'image',
        'email',
        'phone',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}

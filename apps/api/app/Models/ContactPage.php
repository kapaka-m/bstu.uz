<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class ContactPage extends Model
{
    use HasTranslations;

    protected $fillable = [
        'key',
        'map_embed_url',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];
}

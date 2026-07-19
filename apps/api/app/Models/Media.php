<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = ['disk', 'path', 'filename', 'title', 'alt_text', 'type', 'mime_type', 'size', 'is_public', 'alt_key'];

    protected $casts = [
        'is_public' => 'boolean',
        'size' => 'integer',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApplyPage extends Model
{
    protected $fillable = [
        'key',
        'is_published',
        'settings',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'settings' => 'array',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(ApplyPageTranslation::class);
    }
}

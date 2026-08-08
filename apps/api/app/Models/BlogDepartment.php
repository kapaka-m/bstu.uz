<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class BlogDepartment extends Model
{
    use HasTranslations;

    protected $fillable = [
        'slug',
        'image',
        'email',
        'phone',
        'website_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }
}

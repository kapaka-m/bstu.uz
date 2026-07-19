<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacultyTranslation extends Model
{
    protected $fillable = ['faculty_id', 'locale', 'name', 'short_name', 'description', 'content_sections', 'meta_title', 'meta_description'];

    protected $casts = [
        'content_sections' => 'array',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }
}

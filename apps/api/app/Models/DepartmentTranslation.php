<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentTranslation extends Model
{
    protected $fillable = ['department_id', 'locale', 'name', 'short_name', 'description', 'content_sections', 'meta_title', 'meta_description'];

    protected $casts = [
        'content_sections' => 'array',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}

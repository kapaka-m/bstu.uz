<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogDepartmentTranslation extends Model
{
    protected $fillable = [
        'blog_department_id',
        'locale',
        'name',
        'description',
        'meta_title',
        'meta_description',
    ];

    public function blogDepartment()
    {
        return $this->belongsTo(BlogDepartment::class);
    }
}

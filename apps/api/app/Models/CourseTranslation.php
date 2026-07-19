<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseTranslation extends Model
{
    protected $fillable = ['course_id', 'locale', 'name', 'description'];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}

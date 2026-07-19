<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProgramCourse extends Pivot
{
    protected $table = 'program_courses';

    protected $fillable = ['program_id', 'course_id', 'year', 'semester', 'is_required'];
}

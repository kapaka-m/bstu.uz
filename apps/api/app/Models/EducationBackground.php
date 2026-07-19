<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EducationBackground extends Model
{
    protected $table = 'education_backgrounds';

    protected $fillable = ['student_profile_id', 'institution_name', 'degree_obtained', 'gpa', 'graduation_year'];

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class);
    }
}

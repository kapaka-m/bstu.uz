<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = ['student_profile_id', 'program_id', 'student_number', 'academic_year', 'status'];

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}

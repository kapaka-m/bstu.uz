<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = [
        'application_id',
        'admission_id',
        'student_profile_id',
        'program_id',
        'student_number',
        'academic_year',
        'issue_date',
        'status',
        'document_path',
        'issued_by',
        'issued_at',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'issued_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function admission()
    {
        return $this->belongsTo(Admission::class);
    }

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}

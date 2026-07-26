<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prikaz extends Model
{
    protected $table = 'prikazes';

    protected $fillable = [
        'application_id',
        'enrollment_id',
        'student_profile_id',
        'program_id',
        'prikaz_number',
        'issue_date',
        'academic_year',
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

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
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

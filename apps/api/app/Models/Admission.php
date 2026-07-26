<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Admission extends Model
{
    protected $fillable = [
        'application_id',
        'student_profile_id',
        'faculty_id',
        'program_id',
        'admission_number',
        'issue_date',
        'status',
        'student_type',
        'education_type',
        'study_language',
        'estimated_study_duration',
        'document_path',
        'issued_by',
        'issued_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'issued_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}

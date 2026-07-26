<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationEquivalency extends Model
{
    protected $fillable = [
        'application_id',
        'status',
        'previous_university',
        'previous_country',
        'previous_program',
        'previous_study_language',
        'previous_education_type',
        'completed_years',
        'completed_semesters',
        'completed_credits',
        'accepted_credits',
        'rejected_credits',
        'proposed_entry_year',
        'proposed_entry_semester',
        'estimated_remaining_duration',
        'general_academic_notes',
        'student_review_reason',
        'reviewer_id',
        'result_issued_at',
        'student_responded_at',
    ];

    protected $casts = [
        'result_issued_at' => 'datetime',
        'student_responded_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function courses()
    {
        return $this->hasMany(EquivalencyCourse::class);
    }
}

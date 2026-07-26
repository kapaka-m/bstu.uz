<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentVisaProcess extends Model
{
    protected $fillable = [
        'application_id',
        'student_profile_id',
        'telex_number',
        'telex_status',
        'visa_status',
        'visa_notes',
        'reviewer_id',
        'telex_issued_at',
        'visa_updated_at',
    ];

    protected $casts = [
        'telex_issued_at' => 'datetime',
        'visa_updated_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class);
    }
}

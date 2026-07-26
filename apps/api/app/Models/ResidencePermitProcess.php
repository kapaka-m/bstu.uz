<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResidencePermitProcess extends Model
{
    protected $fillable = [
        'application_id',
        'student_profile_id',
        'status',
        'notes',
        'admin_notes',
        'reviewer_id',
        'issued_at',
        'expires_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'expires_at' => 'date',
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

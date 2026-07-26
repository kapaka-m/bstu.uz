<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HousingRequest extends Model
{
    protected $fillable = [
        'application_id',
        'student_profile_id',
        'requested',
        'status',
        'preferred_room_type',
        'notes',
        'admin_notes',
        'reviewer_id',
        'reviewed_at',
    ];

    protected $casts = [
        'requested' => 'boolean',
        'reviewed_at' => 'datetime',
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

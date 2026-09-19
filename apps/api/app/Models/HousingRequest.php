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
        'pinfl',
        'pinfl_verified_at',
        'pinfl_verified_by',
        'start_month',
    ];

    protected $casts = [
        'requested' => 'boolean',
        'reviewed_at' => 'datetime',
        'pinfl_verified_at' => 'datetime',
        'start_month' => 'date:Y-m-d',
    ];

    public function isCompleted(): bool
    {
        return $this->requested && in_array($this->status, ['APPROVED', 'COMPLETED'], true);
    }

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function payments()
    {
        return $this->hasMany(HousingPayment::class)->orderByDesc('month')->orderByDesc('version');
    }

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class);
    }
}

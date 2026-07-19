<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'gender',
        'birth_date',
        'passport_number',
        'passport_expiry_date',
        'nationality',
        'address',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'passport_expiry_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function guardians()
    {
        return $this->hasMany(Guardian::class);
    }

    public function educationBackgrounds()
    {
        return $this->hasMany(EducationBackground::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function documentRequests()
    {
        return $this->hasMany(DocumentRequest::class);
    }
}

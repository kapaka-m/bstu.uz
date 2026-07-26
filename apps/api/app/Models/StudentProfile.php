<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    protected $fillable = [
        'user_id',
        'full_name_english',
        'phone',
        'alternative_phone',
        'preferred_messenger',
        'telegram_username',
        'gender',
        'birth_date',
        'country_of_birth',
        'place_of_birth',
        'passport_number',
        'passport_type',
        'passport_issue_date',
        'passport_expiry_date',
        'passport_issuing_country',
        'passport_place_of_issue',
        'nationality',
        'address',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'passport_issue_date' => 'date',
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

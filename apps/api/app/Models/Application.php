<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'application_number',
        'student_profile_id',
        'program_id',
        'faculty_id',
        'department_id',
        'degree_level',
        'student_type',
        'language_of_study',
        'study_mode',
        'intended_intake',
        'status',
        'documents_status',
        'equivalency_status',
        'application_fee_status',
        'final_review_status',
        'admission_status',
        'current_step',
        'next_action',
        'final_reviewed_by',
        'final_reviewed_at',
        'rejection_reason',
        'correction_reason',
        'terms_agreed_at',
        'information_confirmed_at',
    ];

    protected $casts = [
        'terms_agreed_at' => 'datetime',
        'information_confirmed_at' => 'datetime',
        'final_reviewed_at' => 'datetime',
    ];

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(ApplicationStatusHistory::class);
    }

    public function documents()
    {
        return $this->hasMany(ApplicationDocument::class);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function payments()
    {
        return $this->hasManyThrough(Payment::class, Contract::class);
    }

    public function documentRequirements()
    {
        return $this->hasMany(DocumentRequirement::class);
    }

    public function equivalency()
    {
        return $this->hasOne(ApplicationEquivalency::class);
    }

    public function applicationFeePayments()
    {
        return $this->hasMany(ApplicationFeePayment::class);
    }

    public function admission()
    {
        return $this->hasOne(Admission::class);
    }

    public function enrollment()
    {
        return $this->hasOne(Enrollment::class);
    }

    public function prikaz()
    {
        return $this->hasOne(Prikaz::class);
    }

    public function visaProcess()
    {
        return $this->hasOne(StudentVisaProcess::class);
    }

    public function housingRequest()
    {
        return $this->hasOne(HousingRequest::class);
    }

    public function residencePermitProcess()
    {
        return $this->hasOne(ResidencePermitProcess::class);
    }

    public function serviceFeePayments()
    {
        return $this->hasMany(ServiceFeePayment::class);
    }
}

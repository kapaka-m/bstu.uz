<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquivalencyCourse extends Model
{
    protected $fillable = [
        'application_equivalency_id',
        'previous_course_name',
        'previous_course_code',
        'previous_credits',
        'matched_university_course',
        'matched_course_code',
        'accepted_credits',
        'course_status',
        'required_action',
        'notes',
    ];

    public function equivalency()
    {
        return $this->belongsTo(ApplicationEquivalency::class, 'application_equivalency_id');
    }
}

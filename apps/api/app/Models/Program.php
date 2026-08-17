<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasTranslations;

    protected $fillable = [
        'faculty_id', 'department_id', 'slug', 'code', 'official_code', 'track', 'degree', 'duration_years',
        'study_mode', 'language_of_study', 'tuition_fee', 'currency', 'image', 'is_active',
        'show_on_homepage', 'homepage_sort_order', 'sort_order',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'program_courses')
            ->withPivot('year', 'semester', 'is_required')
            ->withTimestamps();
    }
}

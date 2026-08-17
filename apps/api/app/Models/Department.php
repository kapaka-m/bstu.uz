<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasTranslations;

    protected $fillable = [
        'faculty_id', 'slug', 'code', 'image', 'icon', 'head_name', 'email',
        'phone', 'reception_time', 'source_url', 'sort_order', 'is_active',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    public function staffProfiles()
    {
        return $this->hasMany(StaffProfile::class);
    }

    public function linkedStaffProfiles()
    {
        return $this->belongsToMany(StaffProfile::class, 'staff_profile_department')
            ->withPivot('sort_order')
            ->withTimestamps();
    }
}

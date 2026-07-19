<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class StaffProfile extends Model
{
    use HasTranslations;

    protected $fillable = ['slug', 'department_id', 'faculty_id', 'photo', 'email', 'phone', 'sort_order', 'is_active'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }
}

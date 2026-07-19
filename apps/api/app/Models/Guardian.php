<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    protected $fillable = ['student_profile_id', 'name', 'relation', 'phone', 'email'];

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class);
    }
}

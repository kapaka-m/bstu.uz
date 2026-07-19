<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentRequest extends Model
{
    protected $fillable = ['student_profile_id', 'request_type', 'status', 'comment'];

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class);
    }
}

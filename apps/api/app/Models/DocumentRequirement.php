<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentRequirement extends Model
{
    protected $fillable = [
        'application_id',
        'program_id',
        'requested_by',
        'degree_level',
        'student_type',
        'document_type',
        'name',
        'description',
        'is_required',
        'is_active',
        'deadline',
        'request_reason',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'deadline' => 'date',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}

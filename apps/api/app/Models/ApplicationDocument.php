<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationDocument extends Model
{
    protected $table = 'application_documents';

    protected $fillable = [
        'application_id',
        'student_profile_id',
        'document_name',
        'document_type',
        'file_path',
        'storage_disk',
        'stored_filename',
        'original_name',
        'mime_type',
        'size',
        'current_version',
        'status',
        'review_status',
        'note',
        'student_notes',
        'reviewer_id',
        'reviewed_at',
        'rejection_reason',
        'internal_admin_notes',
        'previous_document_id',
        'is_required',
    ];

    protected $casts = [
        'size' => 'integer',
        'current_version' => 'integer',
        'reviewed_at' => 'datetime',
        'is_required' => 'boolean',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function studentProfile()
    {
        return $this->belongsTo(StudentProfile::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function previousDocument()
    {
        return $this->belongsTo(self::class, 'previous_document_id');
    }
}

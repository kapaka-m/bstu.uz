<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationDocument extends Model
{
    protected $table = 'application_documents';

    protected $fillable = [
        'application_id',
        'document_name',
        'document_type',
        'file_path',
        'original_name',
        'mime_type',
        'size',
        'status',
        'note',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}

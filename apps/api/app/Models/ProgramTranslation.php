<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramTranslation extends Model
{
    protected $fillable = [
        'program_id', 'locale', 'name', 'description', 'requirements',
        'documents', 'curriculum_summary', 'career_opportunities', 'meta_title', 'meta_description',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}

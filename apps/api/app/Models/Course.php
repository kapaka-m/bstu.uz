<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasTranslations;

    protected $fillable = ['code', 'credits', 'semester', 'is_active'];

    public function programs()
    {
        return $this->belongsToMany(Program::class, 'program_courses')
            ->withPivot('year', 'semester', 'is_required')
            ->withTimestamps();
    }
}

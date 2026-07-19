<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffProfileTranslation extends Model
{
    protected $fillable = ['staff_profile_id', 'locale', 'full_name', 'position', 'bio', 'office'];

    public function staffProfile()
    {
        return $this->belongsTo(StaffProfile::class);
    }
}

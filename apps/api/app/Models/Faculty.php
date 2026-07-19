<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    use HasTranslations;

    protected $fillable = ['slug', 'code', 'image', 'icon', 'sort_order', 'is_active'];

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    public function staffProfiles()
    {
        return $this->hasMany(StaffProfile::class);
    }
}

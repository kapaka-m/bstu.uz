<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranslationKey extends Model
{
    protected $fillable = ['group', 'key', 'description', 'is_system'];

    public function values()
    {
        return $this->hasMany(TranslationValue::class);
    }
}

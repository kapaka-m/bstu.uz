<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TranslationValue extends Model
{
    protected $fillable = ['translation_key_id', 'locale', 'value'];

    public function translationKey()
    {
        return $this->belongsTo(TranslationKey::class);
    }
}

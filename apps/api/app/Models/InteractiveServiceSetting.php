<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class InteractiveServiceSetting extends Model
{
    use HasTranslations;

    protected $fillable = ['key', 'home_limit', 'is_active'];
}

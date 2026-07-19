<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class GreenCampusStat extends Model
{
    use HasTranslations;

    protected $fillable = ['icon', 'sort_order'];
}

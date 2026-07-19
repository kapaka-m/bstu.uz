<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GreenCampusStatTranslation extends Model
{
    protected $fillable = ['green_campus_stat_id', 'locale', 'value', 'label'];

    public function stat()
    {
        return $this->belongsTo(GreenCampusStat::class, 'green_campus_stat_id');
    }
}

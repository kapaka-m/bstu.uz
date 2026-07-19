<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Locale extends Model
{
    protected $fillable = ['code', 'name', 'native_name', 'direction', 'is_active', 'sort_order'];
}

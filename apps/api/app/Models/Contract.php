<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = ['application_id', 'contract_number', 'amount', 'status'];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['contract_id', 'payment_number', 'amount', 'payment_date', 'status'];

    protected $casts = [
        'payment_date' => 'datetime',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}

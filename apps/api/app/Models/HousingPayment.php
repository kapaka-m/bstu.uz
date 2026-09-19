<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HousingPayment extends Model
{
    protected $guarded = ['id'];

    protected $hidden = ['receipt_path'];

    protected $casts = ['month' => 'date:Y-m-d', 'amount' => 'decimal:2', 'reviewed_at' => 'datetime'];

    public function housingRequest()
    {
        return $this->belongsTo(HousingRequest::class);
    }
}

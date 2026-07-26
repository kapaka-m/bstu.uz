<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'contract_id',
        'payment_number',
        'payment_type',
        'amount',
        'currency',
        'payment_date',
        'status',
        'receipt_path',
        'receipt_original_name',
        'receipt_mime_type',
        'receipt_size',
        'reviewer_id',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}

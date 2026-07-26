<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceFeePayment extends Model
{
    protected $fillable = [
        'application_id',
        'payment_number',
        'amount',
        'currency',
        'status',
        'receipt_path',
        'receipt_original_name',
        'receipt_mime_type',
        'receipt_size',
        'reviewer_id',
        'reviewed_at',
        'rejection_reason',
        'paid_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}

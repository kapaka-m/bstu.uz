<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationFeePayment extends Model
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
        'paid_at',
        'reviewer_id',
        'reviewed_at',
        'rejection_reason',
        'internal_admin_notes',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}

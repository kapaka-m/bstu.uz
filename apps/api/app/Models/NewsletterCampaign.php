<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterCampaign extends Model
{
    protected $fillable = [
        'subject',
        'title',
        'message',
        'cta_label',
        'cta_url',
        'locale',
        'sent_count',
        'sent_at',
        'created_by',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}

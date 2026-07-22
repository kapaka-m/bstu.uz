<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactPageTranslation extends Model
{
    protected $fillable = [
        'contact_page_id',
        'locale',
        'content',
    ];

    protected $casts = [
        'content' => 'array',
    ];

    public function contactPage()
    {
        return $this->belongsTo(ContactPage::class);
    }
}

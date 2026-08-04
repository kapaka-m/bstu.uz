<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthEmailTemplateTranslation extends Model
{
    protected $fillable = [
        'auth_email_template_id',
        'locale',
        'subject',
        'brand_name',
        'greeting',
        'intro',
        'action_label',
        'expiry_notice',
        'no_action_notice',
        'salutation',
        'signature',
        'subcopy',
        'footer',
    ];

    public function template()
    {
        return $this->belongsTo(AuthEmailTemplate::class, 'auth_email_template_id');
    }
}

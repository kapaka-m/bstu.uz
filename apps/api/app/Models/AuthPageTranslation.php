<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthPageTranslation extends Model
{
    protected $fillable = [
        'auth_page_id',
        'locale',
        'title',
        'subtitle',
        'email_label',
        'email_placeholder',
        'password_label',
        'password_placeholder',
        'confirm_password_label',
        'confirm_password_placeholder',
        'submit_label',
        'loading_label',
        'forgot_password_label',
        'secondary_text',
        'secondary_action_label',
        'secondary_action_url',
        'success_title',
        'success_message',
        'back_label',
        'show_password_label',
        'hide_password_label',
        'validation_required_message',
        'validation_mismatch_message',
        'error_message',
        'logo_alt',
    ];

    public function page()
    {
        return $this->belongsTo(AuthPage::class, 'auth_page_id');
    }
}

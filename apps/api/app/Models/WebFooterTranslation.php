<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebFooterTranslation extends Model
{
    protected $fillable = [
        'web_footer_id',
        'locale',
        'logo_alt',
        'description',
        'admissions_badge',
        'admissions_heading',
        'admissions_description',
        'admissions_button_label',
        'newsletter_title',
        'newsletter_description',
        'newsletter_placeholder',
        'newsletter_success_message',
        'useful_links_title',
        'faculties_title',
        'contact_title',
        'address_line_1',
        'address_line_2',
        'phone_label',
        'email_label',
        'rights_text',
        'useful_link_labels',
        'faculty_link_labels',
    ];

    protected $casts = [
        'useful_link_labels' => 'array',
        'faculty_link_labels' => 'array',
    ];

    public function webFooter()
    {
        return $this->belongsTo(WebFooter::class);
    }
}

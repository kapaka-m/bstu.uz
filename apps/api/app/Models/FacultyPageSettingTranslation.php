<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacultyPageSettingTranslation extends Model
{
    protected $fillable = [
        'faculty_page_setting_id',
        'locale',
        'home_label',
        'faculties_label',
        'departments_label',
        'bachelor_programs_label',
        'master_specializations_label',
        'contact_label',
        'overview_label',
        'leadership_label',
        'learn_more_label',
        'head_of_department_label',
        'phone_label',
        'email_label',
        'quick_department_links_label',
        'dean_contact_label',
        'deputy_dean_contacts_label',
        'industry_cooperation_label',
        'academic_pathways_label',
        'leadership_description',
        'departments_description',
        'bachelor_description',
        'master_description',
        'contact_description',
        'not_found_title_label',
        'not_found_description',
    ];

    public function setting()
    {
        return $this->belongsTo(FacultyPageSetting::class, 'faculty_page_setting_id');
    }
}

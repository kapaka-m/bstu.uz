<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentPageSettingTranslation extends Model
{
    protected $fillable = [
        'department_page_setting_id',
        'locale',
        'home_label',
        'faculties_label',
        'quick_contact_label',
        'back_to_faculty_label',
        'head_of_department_label',
        'history_label',
        'prepared_specialists_label',
        'subjects_label',
        'staff_label',
        'publications_label',
        'research_label',
        'cooperation_label',
        'activities_label',
        'bachelor_label',
        'master_label',
        'doctoral_label',
        'programs_label',
        'bachelor_subjects_label',
        'master_subjects_label',
        'gallery_label',
        'conference_papers_label',
        'scopus_web_of_science_label',
        'textbooks_manuals_label',
        'textbooks_manuals_monographs_label',
        'monographs_label',
        'articles_label',
        'not_found_title_label',
        'not_found_description',
    ];

    public function setting()
    {
        return $this->belongsTo(DepartmentPageSetting::class, 'department_page_setting_id');
    }
}

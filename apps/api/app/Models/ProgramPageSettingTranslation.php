<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramPageSettingTranslation extends Model
{
    protected $fillable = [
        'program_page_setting_id',
        'locale',
        'back_to_programs_label',
        'course_curriculum_label',
        'courses_label',
        'year_label',
        'semester_label',
        'admission_requirements_label',
        'documents_label',
        'quick_facts_label',
        'program_code_label',
        'degree_level_label',
        'duration_label',
        'years_label',
        'language_of_instruction_label',
        'study_mode_label',
        'tuition_fee_label',
        'intake_period_label',
        'intake_date_label',
        'parent_faculty_label',
        'parent_department_label',
        'program_coordinator_label',
        'academic_staff_label',
        'faculty_helpdesk_label',
        'faculty_helpdesk_description',
        'contact_university_label',
        'career_opportunities_label',
        'apply_now_label',
        'apply_description',
        'not_found_title_label',
        'not_found_description',
        'no_details_label',
        'degree_bachelor_label',
        'degree_master_label',
        'degree_phd_label',
        'mode_full_time_label',
        'mode_part_time_label',
        'mode_evening_label',
        'mode_distance_label',
        'language_english_label',
        'language_uzbek_label',
        'language_russian_label',
        'language_arabic_label',
    ];

    public function setting()
    {
        return $this->belongsTo(ProgramPageSetting::class, 'program_page_setting_id');
    }
}

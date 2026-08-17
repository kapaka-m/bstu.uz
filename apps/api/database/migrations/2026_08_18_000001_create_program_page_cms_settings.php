<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $fields = [
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

    public function up(): void
    {
        if (! Schema::hasTable('program_page_settings')) {
            Schema::create('program_page_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique()->default('main');
                $table->boolean('is_active')->default(true);
                $table->json('settings')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('program_page_setting_translations')) {
            Schema::create('program_page_setting_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('program_page_setting_id');
                $table->string('locale', 10)->index();
                foreach ($this->fields as $field) {
                    $table->text($field)->nullable();
                }
                $table->timestamps();
                $table->foreign('program_page_setting_id', 'program_page_cms_setting_fk')
                    ->references('id')
                    ->on('program_page_settings')
                    ->cascadeOnDelete();
                $table->unique(['program_page_setting_id', 'locale'], 'program_page_setting_locale_unique');
            });
        }

        $this->seedDefaults();
        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        Schema::dropIfExists('program_page_setting_translations');
        Schema::dropIfExists('program_page_settings');
    }

    private function seedDefaults(): void
    {
        $now = now();
        $settingId = DB::table('program_page_settings')->where('key', 'main')->value('id');

        if (! $settingId) {
            $settingId = DB::table('program_page_settings')->insertGetId([
                'key' => 'main',
                'is_active' => true,
                'settings' => json_encode([]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ($this->defaultTranslations() as $locale => $fields) {
            $existing = DB::table('program_page_setting_translations')
                ->where('program_page_setting_id', $settingId)
                ->where('locale', $locale)
                ->first();

            if (! $existing) {
                DB::table('program_page_setting_translations')->insert($fields + [
                    'program_page_setting_id' => $settingId,
                    'locale' => $locale,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                continue;
            }

            $missingFields = [];
            foreach ($fields as $field => $value) {
                if (($existing->{$field} ?? null) === null || trim((string) $existing->{$field}) === '') {
                    $missingFields[$field] = $value;
                }
            }

            if ($missingFields !== []) {
                DB::table('program_page_setting_translations')
                    ->where('id', $existing->id)
                    ->update($missingFields + ['updated_at' => $now]);
            }
        }
    }

    private function defaultTranslations(): array
    {
        return [
            'en' => [
                'back_to_programs_label' => 'Back to Programs',
                'course_curriculum_label' => 'Course Curriculum',
                'courses_label' => 'Courses',
                'year_label' => 'Year',
                'semester_label' => 'Semester',
                'admission_requirements_label' => 'Admission Requirements',
                'documents_label' => 'Required Documents',
                'quick_facts_label' => 'Quick Facts',
                'program_code_label' => 'Program code',
                'degree_level_label' => 'Degree Level',
                'duration_label' => 'Duration',
                'years_label' => 'years',
                'language_of_instruction_label' => 'Language of Instruction',
                'study_mode_label' => 'Study mode',
                'tuition_fee_label' => 'Tuition Fee',
                'intake_period_label' => 'Intake Period',
                'intake_date_label' => 'September Intake',
                'parent_faculty_label' => 'Parent Faculty',
                'parent_department_label' => 'Parent Department',
                'program_coordinator_label' => 'Program Coordinator',
                'academic_staff_label' => 'Academic Staff',
                'faculty_helpdesk_label' => 'Faculty Helpdesk',
                'faculty_helpdesk_description' => 'Contact the university for admission, program, and documentation support.',
                'contact_university_label' => 'Contact University',
                'career_opportunities_label' => 'Career Opportunities',
                'apply_now_label' => 'Apply Now',
                'apply_description' => 'Start your application and submit the required documents through the admission system.',
                'not_found_title_label' => 'Program Not Found',
                'not_found_description' => 'The requested program page could not be found.',
                'no_details_label' => 'No additional program details have been published yet.',
                'degree_bachelor_label' => 'Bachelor',
                'degree_master_label' => 'Master',
                'degree_phd_label' => 'PhD',
                'mode_full_time_label' => 'Full-time',
                'mode_part_time_label' => 'Part-time',
                'mode_evening_label' => 'Evening',
                'mode_distance_label' => 'Distance',
                'language_english_label' => 'English',
                'language_uzbek_label' => 'Uzbek',
                'language_russian_label' => 'Russian',
                'language_arabic_label' => 'Arabic',
            ],
            'uz' => [
                'back_to_programs_label' => 'Dasturlarga qaytish',
                'course_curriculum_label' => 'O‘quv rejasi',
                'courses_label' => 'Fanlar',
                'year_label' => 'Yil',
                'semester_label' => 'Semestr',
                'admission_requirements_label' => 'Qabul talablari',
                'documents_label' => 'Kerakli hujjatlar',
                'quick_facts_label' => 'Qisqa ma’lumotlar',
                'program_code_label' => 'Dastur kodi',
                'degree_level_label' => 'Ta’lim darajasi',
                'duration_label' => 'Davomiyligi',
                'years_label' => 'yil',
                'language_of_instruction_label' => 'Ta’lim tili',
                'study_mode_label' => 'Ta’lim shakli',
                'tuition_fee_label' => 'Kontrakt to‘lovi',
                'intake_period_label' => 'Qabul davri',
                'intake_date_label' => 'Sentyabr qabuli',
                'parent_faculty_label' => 'Tegishli fakultet',
                'parent_department_label' => 'Tegishli kafedra',
                'program_coordinator_label' => 'Dastur koordinatori',
                'academic_staff_label' => 'Akademik xodimlar',
                'faculty_helpdesk_label' => 'Fakultet yordam xizmati',
                'faculty_helpdesk_description' => 'Qabul, dastur va hujjatlar bo‘yicha yordam olish uchun universitet bilan bog‘laning.',
                'contact_university_label' => 'Universitet bilan bog‘lanish',
                'career_opportunities_label' => 'Kasbiy imkoniyatlar',
                'apply_now_label' => 'Ariza topshirish',
                'apply_description' => 'Qabul tizimi orqali arizangizni boshlang va kerakli hujjatlarni yuboring.',
                'not_found_title_label' => 'Dastur topilmadi',
                'not_found_description' => 'So‘ralgan dastur sahifasi topilmadi.',
                'no_details_label' => 'Hozircha qo‘shimcha dastur ma’lumotlari e’lon qilinmagan.',
                'degree_bachelor_label' => 'Bakalavr',
                'degree_master_label' => 'Magistr',
                'degree_phd_label' => 'PhD',
                'mode_full_time_label' => 'Kunduzgi ta’lim',
                'mode_part_time_label' => 'Sirtqi ta’lim',
                'mode_evening_label' => 'Kechki ta’lim',
                'mode_distance_label' => 'Masofaviy ta’lim',
                'language_english_label' => 'Ingliz tili',
                'language_uzbek_label' => 'O‘zbek tili',
                'language_russian_label' => 'Rus tili',
                'language_arabic_label' => 'Arab tili',
            ],
            'ru' => [
                'back_to_programs_label' => 'Вернуться к программам',
                'course_curriculum_label' => 'Учебный план',
                'courses_label' => 'Дисциплины',
                'year_label' => 'Год',
                'semester_label' => 'Семестр',
                'admission_requirements_label' => 'Требования к поступлению',
                'documents_label' => 'Необходимые документы',
                'quick_facts_label' => 'Краткая информация',
                'program_code_label' => 'Код программы',
                'degree_level_label' => 'Уровень образования',
                'duration_label' => 'Продолжительность',
                'years_label' => 'лет',
                'language_of_instruction_label' => 'Язык обучения',
                'study_mode_label' => 'Форма обучения',
                'tuition_fee_label' => 'Стоимость обучения',
                'intake_period_label' => 'Период приема',
                'intake_date_label' => 'Сентябрьский набор',
                'parent_faculty_label' => 'Факультет',
                'parent_department_label' => 'Кафедра',
                'program_coordinator_label' => 'Координатор программы',
                'academic_staff_label' => 'Преподаватели',
                'faculty_helpdesk_label' => 'Служба поддержки факультета',
                'faculty_helpdesk_description' => 'Свяжитесь с университетом для консультации по поступлению, программе и документам.',
                'contact_university_label' => 'Связаться с университетом',
                'career_opportunities_label' => 'Карьерные возможности',
                'apply_now_label' => 'Подать заявку',
                'apply_description' => 'Начните подачу заявки и отправьте необходимые документы через систему приема.',
                'not_found_title_label' => 'Программа не найдена',
                'not_found_description' => 'Запрошенная страница программы не найдена.',
                'no_details_label' => 'Дополнительные сведения о программе пока не опубликованы.',
                'degree_bachelor_label' => 'Бакалавр',
                'degree_master_label' => 'Магистр',
                'degree_phd_label' => 'PhD',
                'mode_full_time_label' => 'Очная форма',
                'mode_part_time_label' => 'Заочная форма',
                'mode_evening_label' => 'Вечерняя форма',
                'mode_distance_label' => 'Дистанционная форма',
                'language_english_label' => 'Английский язык',
                'language_uzbek_label' => 'Узбекский язык',
                'language_russian_label' => 'Русский язык',
                'language_arabic_label' => 'Арабский язык',
            ],
            'ar' => [
                'back_to_programs_label' => 'العودة إلى البرامج',
                'course_curriculum_label' => 'الخطة الدراسية',
                'courses_label' => 'المقررات',
                'year_label' => 'السنة',
                'semester_label' => 'الفصل',
                'admission_requirements_label' => 'متطلبات القبول',
                'documents_label' => 'المستندات المطلوبة',
                'quick_facts_label' => 'معلومات سريعة',
                'program_code_label' => 'كود البرنامج',
                'degree_level_label' => 'الدرجة العلمية',
                'duration_label' => 'المدة',
                'years_label' => 'سنوات',
                'language_of_instruction_label' => 'لغة الدراسة',
                'study_mode_label' => 'نظام الدراسة',
                'tuition_fee_label' => 'الرسوم الدراسية',
                'intake_period_label' => 'فترة القبول',
                'intake_date_label' => 'قبول سبتمبر',
                'parent_faculty_label' => 'الكلية التابعة',
                'parent_department_label' => 'القسم التابع',
                'program_coordinator_label' => 'منسق البرنامج',
                'academic_staff_label' => 'أعضاء هيئة التدريس',
                'faculty_helpdesk_label' => 'مكتب مساعدة الكلية',
                'faculty_helpdesk_description' => 'تواصل مع الجامعة للحصول على دعم القبول والبرنامج والمستندات.',
                'contact_university_label' => 'التواصل مع الجامعة',
                'career_opportunities_label' => 'فرص العمل',
                'apply_now_label' => 'قدّم الآن',
                'apply_description' => 'ابدأ طلبك وأرسل المستندات المطلوبة عبر نظام القبول.',
                'not_found_title_label' => 'لم يتم العثور على البرنامج',
                'not_found_description' => 'تعذر العثور على صفحة البرنامج المطلوبة.',
                'no_details_label' => 'لم يتم نشر تفاصيل إضافية لهذا البرنامج بعد.',
                'degree_bachelor_label' => 'بكالوريوس',
                'degree_master_label' => 'ماجستير',
                'degree_phd_label' => 'دكتوراه',
                'mode_full_time_label' => 'دوام كامل',
                'mode_part_time_label' => 'دوام جزئي',
                'mode_evening_label' => 'الدراسة المسائية',
                'mode_distance_label' => 'التعليم عن بعد',
                'language_english_label' => 'اللغة الإنجليزية',
                'language_uzbek_label' => 'اللغة الأوزبكية',
                'language_russian_label' => 'اللغة الروسية',
                'language_arabic_label' => 'اللغة العربية',
            ],
        ];
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $fields = [
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

    public function up(): void
    {
        if (! Schema::hasTable('department_page_settings')) {
            Schema::create('department_page_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique()->default('main');
                $table->boolean('is_active')->default(true);
                $table->json('settings')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('department_page_setting_translations')) {
            Schema::create('department_page_setting_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('department_page_setting_id');
                $table->string('locale', 10)->index();
                foreach ($this->fields as $field) {
                    $table->text($field)->nullable();
                }
                $table->timestamps();
                $table->foreign('department_page_setting_id', 'department_page_cms_setting_fk')
                    ->references('id')
                    ->on('department_page_settings')
                    ->cascadeOnDelete();
                $table->unique(['department_page_setting_id', 'locale'], 'department_page_setting_locale_unique');
            });
        }

        $this->seedDefaults();
        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        Schema::dropIfExists('department_page_setting_translations');
        Schema::dropIfExists('department_page_settings');
    }

    private function seedDefaults(): void
    {
        $now = now();
        $settingId = DB::table('department_page_settings')->where('key', 'main')->value('id');

        if (! $settingId) {
            $settingId = DB::table('department_page_settings')->insertGetId([
                'key' => 'main',
                'is_active' => true,
                'settings' => json_encode([]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ($this->defaultTranslations() as $locale => $fields) {
            $existing = DB::table('department_page_setting_translations')
                ->where('department_page_setting_id', $settingId)
                ->where('locale', $locale)
                ->first();

            if (! $existing) {
                DB::table('department_page_setting_translations')->insert(
                    $fields + [
                        'department_page_setting_id' => $settingId,
                        'locale' => $locale,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                );
                continue;
            }

            $missingFields = [];
            foreach ($fields as $field => $value) {
                if (($existing->{$field} ?? null) === null || trim((string) $existing->{$field}) === '') {
                    $missingFields[$field] = $value;
                }
            }

            if ($missingFields !== []) {
                DB::table('department_page_setting_translations')
                    ->where('id', $existing->id)
                    ->update($missingFields + ['updated_at' => $now]);
            }
        }
    }

    private function defaultTranslations(): array
    {
        return [
            'en' => [
                'home_label' => 'Home',
                'faculties_label' => 'Faculties',
                'quick_contact_label' => 'Quick Contact',
                'back_to_faculty_label' => 'Back to Faculty',
                'head_of_department_label' => 'Head of Department',
                'history_label' => 'Department History',
                'prepared_specialists_label' => 'Prepared Specialists',
                'subjects_label' => 'Taught Subjects',
                'staff_label' => 'Professor-Teachers',
                'publications_label' => 'Textbooks, Manuals and Monographs',
                'research_label' => 'Ongoing Research',
                'cooperation_label' => 'Cooperation / International Relations',
                'activities_label' => 'News / Activities / Prospective Plans',
                'bachelor_label' => 'Bachelor Programs',
                'master_label' => 'Master Programs',
                'doctoral_label' => 'Doctoral Programs',
                'programs_label' => 'Programs',
                'bachelor_subjects_label' => 'Bachelor subjects',
                'master_subjects_label' => 'Master subjects',
                'gallery_label' => 'Gallery',
                'conference_papers_label' => 'Conference Papers',
                'scopus_web_of_science_label' => 'Scopus / Web of Science Articles',
                'textbooks_manuals_label' => 'Textbooks / Manuals',
                'textbooks_manuals_monographs_label' => 'Textbooks, Manuals and Monographs',
                'monographs_label' => 'Monographs',
                'articles_label' => 'Scientific Articles',
                'not_found_title_label' => 'Department Not Found',
                'not_found_description' => 'The requested department page could not be found.',
            ],
            'uz' => [
                'home_label' => 'Bosh sahifa',
                'faculties_label' => 'Fakultetlar',
                'quick_contact_label' => 'Tezkor aloqa',
                'back_to_faculty_label' => 'Fakultetga qaytish',
                'head_of_department_label' => 'Kafedra mudiri',
                'history_label' => 'Kafedra tarixi',
                'prepared_specialists_label' => 'Tayyorlanadigan mutaxassislar',
                'subjects_label' => 'O‘qitiladigan fanlar',
                'staff_label' => 'Professor-o‘qituvchilar',
                'publications_label' => 'Darsliklar, o‘quv qo‘llanmalar va monografiyalar',
                'research_label' => 'Joriy tadqiqotlar',
                'cooperation_label' => 'Hamkorlik / xalqaro aloqalar',
                'activities_label' => 'Yangiliklar / faoliyat / istiqbolli rejalar',
                'bachelor_label' => 'Bakalavr dasturlari',
                'master_label' => 'Magistratura dasturlari',
                'doctoral_label' => 'Doktorantura dasturlari',
                'programs_label' => 'Dasturlar',
                'bachelor_subjects_label' => 'Bakalavr fanlari',
                'master_subjects_label' => 'Magistratura fanlari',
                'gallery_label' => 'Galereya',
                'conference_papers_label' => 'Konferensiya maqolalari',
                'scopus_web_of_science_label' => 'Scopus / Web of Science maqolalari',
                'textbooks_manuals_label' => 'Darsliklar / qo‘llanmalar',
                'textbooks_manuals_monographs_label' => 'Darsliklar, o‘quv qo‘llanmalar va monografiyalar',
                'monographs_label' => 'Monografiyalar',
                'articles_label' => 'Ilmiy maqolalar',
                'not_found_title_label' => 'Kafedra topilmadi',
                'not_found_description' => 'So‘ralgan kafedra sahifasi topilmadi.',
            ],
            'ru' => [
                'home_label' => 'Главная',
                'faculties_label' => 'Факультеты',
                'quick_contact_label' => 'Быстрый контакт',
                'back_to_faculty_label' => 'Вернуться к факультету',
                'head_of_department_label' => 'Заведующий кафедрой',
                'history_label' => 'История кафедры',
                'prepared_specialists_label' => 'Подготавливаемые специалисты',
                'subjects_label' => 'Изучаемые дисциплины',
                'staff_label' => 'Профессорско-преподавательский состав',
                'publications_label' => 'Учебники, пособия и монографии',
                'research_label' => 'Текущие исследования',
                'cooperation_label' => 'Сотрудничество / международные связи',
                'activities_label' => 'Новости / деятельность / перспективные планы',
                'bachelor_label' => 'Программы бакалавриата',
                'master_label' => 'Программы магистратуры',
                'doctoral_label' => 'Докторские программы',
                'programs_label' => 'Программы',
                'bachelor_subjects_label' => 'Дисциплины бакалавриата',
                'master_subjects_label' => 'Дисциплины магистратуры',
                'gallery_label' => 'Галерея',
                'conference_papers_label' => 'Материалы конференций',
                'scopus_web_of_science_label' => 'Статьи Scopus / Web of Science',
                'textbooks_manuals_label' => 'Учебники / пособия',
                'textbooks_manuals_monographs_label' => 'Учебники, пособия и монографии',
                'monographs_label' => 'Монографии',
                'articles_label' => 'Научные статьи',
                'not_found_title_label' => 'Кафедра не найдена',
                'not_found_description' => 'Запрошенная страница кафедры не найдена.',
            ],
            'ar' => [
                'home_label' => 'الرئيسية',
                'faculties_label' => 'الكليات',
                'quick_contact_label' => 'تواصل سريع',
                'back_to_faculty_label' => 'العودة إلى الكلية',
                'head_of_department_label' => 'رئيس القسم',
                'history_label' => 'تاريخ القسم',
                'prepared_specialists_label' => 'التخصصات التي يتم إعدادها',
                'subjects_label' => 'المواد الدراسية',
                'staff_label' => 'أعضاء هيئة التدريس',
                'publications_label' => 'الكتب والمراجع والمؤلفات العلمية',
                'research_label' => 'الأبحاث الجارية',
                'cooperation_label' => 'التعاون / العلاقات الدولية',
                'activities_label' => 'الأخبار / الأنشطة / الخطط المستقبلية',
                'bachelor_label' => 'برامج البكالوريوس',
                'master_label' => 'برامج الماجستير',
                'doctoral_label' => 'برامج الدكتوراه',
                'programs_label' => 'البرامج',
                'bachelor_subjects_label' => 'مواد البكالوريوس',
                'master_subjects_label' => 'مواد الماجستير',
                'gallery_label' => 'المعرض',
                'conference_papers_label' => 'أوراق المؤتمرات',
                'scopus_web_of_science_label' => 'مقالات Scopus / Web of Science',
                'textbooks_manuals_label' => 'الكتب / المراجع',
                'textbooks_manuals_monographs_label' => 'الكتب والمراجع والمؤلفات العلمية',
                'monographs_label' => 'المؤلفات العلمية',
                'articles_label' => 'المقالات العلمية',
                'not_found_title_label' => 'لم يتم العثور على القسم',
                'not_found_description' => 'تعذر العثور على صفحة القسم المطلوبة.',
            ],
        ];
    }
};

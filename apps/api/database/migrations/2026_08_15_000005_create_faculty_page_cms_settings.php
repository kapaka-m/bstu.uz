<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $fields = [
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
    ];

    public function up(): void
    {
        if (! Schema::hasTable('faculty_page_settings')) {
            Schema::create('faculty_page_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique()->default('main');
                $table->boolean('is_active')->default(true);
                $table->json('settings')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('faculty_page_setting_translations')) {
            Schema::create('faculty_page_setting_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('faculty_page_setting_id');
                $table->string('locale', 10)->index();
                foreach ($this->fields as $field) {
                    $table->text($field)->nullable();
                }
                $table->timestamps();
                $table->foreign('faculty_page_setting_id', 'faculty_page_cms_setting_fk')
                    ->references('id')
                    ->on('faculty_page_settings')
                    ->cascadeOnDelete();
                $table->unique(['faculty_page_setting_id', 'locale'], 'faculty_page_setting_locale_unique');
            });
        }

        $this->seedDefaults();
    }

    public function down(): void
    {
        Schema::dropIfExists('faculty_page_setting_translations');
        Schema::dropIfExists('faculty_page_settings');
    }

    private function seedDefaults(): void
    {
        $now = now();
        $settingId = DB::table('faculty_page_settings')->where('key', 'main')->value('id');

        if (! $settingId) {
            $settingId = DB::table('faculty_page_settings')->insertGetId([
                'key' => 'main',
                'is_active' => true,
                'settings' => json_encode([]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach ($this->defaultTranslations() as $locale => $fields) {
            $existing = DB::table('faculty_page_setting_translations')
                ->where('faculty_page_setting_id', $settingId)
                ->where('locale', $locale)
                ->first();

            if (! $existing) {
                DB::table('faculty_page_setting_translations')->insert(
                    $fields + [
                        'faculty_page_setting_id' => $settingId,
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
                DB::table('faculty_page_setting_translations')
                    ->where('id', $existing->id)
                    ->update($missingFields + [
                        'updated_at' => $now,
                    ]);
            }
        }
    }

    private function defaultTranslations(): array
    {
        return [
            'en' => [
                'home_label' => 'Home',
                'faculties_label' => 'Faculties',
                'departments_label' => 'Departments',
                'bachelor_programs_label' => "Bachelor's Programs",
                'master_specializations_label' => "Master's Specializations",
                'contact_label' => 'Contact',
                'overview_label' => 'About the Faculty',
                'leadership_label' => 'Faculty Leadership',
                'learn_more_label' => 'Learn more',
                'head_of_department_label' => 'Head of Department',
                'phone_label' => 'Phone',
                'email_label' => 'Email',
                'quick_department_links_label' => 'Quick Department Links',
                'dean_contact_label' => 'Dean Contact',
                'deputy_dean_contacts_label' => 'Deputy Dean Contacts',
                'industry_cooperation_label' => 'Industry Cooperation',
                'academic_pathways_label' => 'Academic Pathways',
                'leadership_description' => 'Faculty leaders coordinate academic quality, student support, research activity, and partnerships.',
                'departments_description' => 'Specialized departments connect academic training with applied research, professional practice, and modern industry preparation.',
                'bachelor_description' => "Bachelor's programs offered by the faculty.",
                'master_description' => "Master's and doctoral specializations offered by the faculty.",
                'contact_description' => 'Faculty and department contact paths for students, applicants, and partners.',
            ],
            'uz' => [
                'home_label' => 'Bosh sahifa',
                'faculties_label' => 'Fakultetlar',
                'departments_label' => 'Kafedralar',
                'bachelor_programs_label' => 'Bakalavr dasturlari',
                'master_specializations_label' => 'Magistratura mutaxassisliklari',
                'contact_label' => 'Aloqa',
                'overview_label' => 'Fakultet haqida',
                'leadership_label' => 'Fakultet rahbariyati',
                'learn_more_label' => 'Batafsil',
                'head_of_department_label' => 'Kafedra mudiri',
                'phone_label' => 'Telefon',
                'email_label' => 'Email',
                'quick_department_links_label' => 'Kafedralarga tezkor havolalar',
                'dean_contact_label' => 'Dekan bilan aloqa',
                'deputy_dean_contacts_label' => 'Dekan o‘rinbosarlari bilan aloqa',
                'industry_cooperation_label' => 'Sanoat hamkorligi',
                'academic_pathways_label' => 'Akademik yo‘nalishlar',
                'leadership_description' => 'Fakultet rahbariyati ta’lim sifati, talabalarni qo‘llab-quvvatlash, ilmiy faoliyat va hamkorlikni muvofiqlashtiradi.',
                'departments_description' => 'Ixtisoslashgan kafedralar ta’lim jarayonini amaliy tadqiqotlar, kasbiy amaliyot va zamonaviy ishlab chiqarish tayyorgarligi bilan bog‘laydi.',
                'bachelor_description' => 'Fakultet tomonidan taklif etiladigan bakalavr dasturlari.',
                'master_description' => 'Fakultet tomonidan taklif etiladigan magistratura va doktorantura mutaxassisliklari.',
                'contact_description' => 'Talabalar, abituriyentlar va hamkorlar uchun fakultet hamda kafedralar bilan aloqa ma’lumotlari.',
            ],
            'ru' => [
                'home_label' => 'Главная',
                'faculties_label' => 'Факультеты',
                'departments_label' => 'Кафедры',
                'bachelor_programs_label' => 'Программы бакалавриата',
                'master_specializations_label' => 'Магистерские специальности',
                'contact_label' => 'Контакты',
                'overview_label' => 'О факультете',
                'leadership_label' => 'Руководство факультета',
                'learn_more_label' => 'Подробнее',
                'head_of_department_label' => 'Заведующий кафедрой',
                'phone_label' => 'Телефон',
                'email_label' => 'Email',
                'quick_department_links_label' => 'Быстрые ссылки на кафедры',
                'dean_contact_label' => 'Контакт декана',
                'deputy_dean_contacts_label' => 'Контакты заместителей декана',
                'industry_cooperation_label' => 'Сотрудничество с индустрией',
                'academic_pathways_label' => 'Академические направления',
                'leadership_description' => 'Руководство факультета координирует качество образования, поддержку студентов, научную деятельность и партнерства.',
                'departments_description' => 'Профильные кафедры объединяют академическую подготовку с прикладными исследованиями, профессиональной практикой и современной отраслевой подготовкой.',
                'bachelor_description' => 'Программы бакалавриата, предлагаемые факультетом.',
                'master_description' => 'Магистерские и докторские специальности, предлагаемые факультетом.',
                'contact_description' => 'Контактные каналы факультета и кафедр для студентов, абитуриентов и партнеров.',
            ],
            'ar' => [
                'home_label' => 'الرئيسية',
                'faculties_label' => 'الكليات',
                'departments_label' => 'الأقسام',
                'bachelor_programs_label' => 'برامج البكالوريوس',
                'master_specializations_label' => 'تخصصات الماجستير',
                'contact_label' => 'التواصل',
                'overview_label' => 'عن الكلية',
                'leadership_label' => 'إدارة الكلية',
                'learn_more_label' => 'اعرف المزيد',
                'head_of_department_label' => 'رئيس القسم',
                'phone_label' => 'الهاتف',
                'email_label' => 'البريد الإلكتروني',
                'quick_department_links_label' => 'روابط الأقسام السريعة',
                'dean_contact_label' => 'التواصل مع العميد',
                'deputy_dean_contacts_label' => 'التواصل مع نواب العميد',
                'industry_cooperation_label' => 'التعاون الصناعي',
                'academic_pathways_label' => 'المسارات الأكاديمية',
                'leadership_description' => 'تنسق إدارة الكلية جودة التعليم ودعم الطلاب والنشاط البحثي والشراكات.',
                'departments_description' => 'تربط الأقسام المتخصصة التعليم الأكاديمي بالبحث التطبيقي والممارسة المهنية والإعداد الحديث لسوق العمل.',
                'bachelor_description' => 'برامج البكالوريوس التي تقدمها الكلية.',
                'master_description' => 'تخصصات الماجستير والدكتوراه التي تقدمها الكلية.',
                'contact_description' => 'قنوات التواصل مع الكلية والأقسام للطلاب والمتقدمين والشركاء.',
            ],
        ];
    }
};

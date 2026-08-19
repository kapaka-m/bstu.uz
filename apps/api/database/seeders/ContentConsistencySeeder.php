<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContentConsistencySeeder extends Seeder
{
    public function run(): void
    {
        $this->syncServiceAndDigitalization();
        $this->syncEngineering();
        $this->runMigrationCorrection('2026_08_10_000002_normalize_technology_faculty_content_programs.php');
        $this->runMigrationCorrection('2026_08_10_000003_normalize_natural_resources_faculty_content_programs.php');
        $this->runMigrationCorrection('2026_08_10_000004_normalize_service_digitalization_faculty_content_programs.php');
        $this->runMigrationCorrection('2026_08_11_000001_add_homepage_controls_to_programs.php');
        $this->runMigrationCorrection('2026_08_11_000002_normalize_homepage_cms_sources.php');
        $this->runMigrationCorrection('2026_08_11_000003_normalize_about_page_cms_sources.php');
        $this->runMigrationCorrection('2026_08_11_000004_normalize_announcement_cms_sources.php');
        $this->runMigrationCorrection('2026_08_11_000005_normalize_news_cms_sources.php');
        $this->runMigrationCorrection('2026_08_11_000006_normalize_blog_cms_sources.php');
        $this->runMigrationCorrection('2026_08_11_000007_normalize_video_gallery_cms_sources.php');
        $this->runMigrationCorrection('2026_08_11_000008_normalize_green_campus_cms_sources.php');
        $this->runMigrationCorrection('2026_08_11_000009_assign_technology_ochilov_head_photo.php');
        $this->runMigrationCorrection('2026_08_11_000010_assign_natural_resources_hydrotechnical_head_avatar.php');
        $this->runMigrationCorrection('2026_08_11_000011_sync_service_digitalization_header_departments.php');
        $this->runMigrationCorrection('2026_08_11_000012_normalize_university_centers_content.php');
        $this->runMigrationCorrection('2026_08_12_000002_sync_academic_localized_content.php');
        $this->runMigrationCorrection('2026_08_12_000003_remove_unused_about_translation_keys.php');
        $this->runMigrationCorrection('2026_08_13_000001_add_blog_comment_placeholders_to_settings.php');
        $this->runMigrationCorrection('2026_08_13_000002_remove_unused_contact_translation_keys.php');
        $this->runMigrationCorrection('2026_08_13_000003_remove_unused_contact_common_translation_keys.php');
        $this->runMigrationCorrection('2026_08_13_000004_remove_unused_administration_profile_translation_keys.php');
        $this->runMigrationCorrection('2026_08_13_000005_remove_unused_translation_table_keys.php');
        $this->runMigrationCorrection('2026_08_13_000006_add_missing_runtime_translation_keys.php');
        $this->runMigrationCorrection('2026_08_14_000001_remove_unused_auth_translation_keys.php');
        $this->runMigrationCorrection('2026_08_14_000002_activate_pdf_matching_daytime_programs.php');
        $this->runMigrationCorrection('2026_08_14_000003_normalize_pdf_matching_daytime_program_duplicates.php');
        $this->runMigrationCorrection('2026_08_14_000004_normalize_pdf_daytime_program_names.php');
        $this->runMigrationCorrection('2026_08_14_000005_add_missing_pdf_daytime_programs.php');
        $this->runMigrationCorrection('2026_08_14_000006_delete_non_pdf_daytime_programs.php');
        $this->runMigrationCorrection('2026_08_14_000007_complete_pdf_program_translations.php');
        $this->runMigrationCorrection('2026_08_14_000008_localize_arabic_staff_profile_names.php');
        $this->runMigrationCorrection('2026_08_14_000009_refine_arabic_engineering_staff_names.php');
        $this->runMigrationCorrection('2026_08_15_000001_clean_academic_arabic_translation_leaks.php');
        $this->runMigrationCorrection('2026_08_15_000002_clean_remaining_arabic_academic_labels.php');
        $this->runMigrationCorrection('2026_08_15_000003_localize_russian_staff_names_and_academic_sections.php');
        $this->runMigrationCorrection('2026_08_15_000004_normalize_engineering_department_localized_sections.php');
        $this->runMigrationCorrection('2026_08_15_000005_create_faculty_page_cms_settings.php');
        $this->runMigrationCorrection('2026_08_15_000006_ensure_faculty_page_cms_foreign_key.php');
        $this->runMigrationCorrection('2026_08_15_000007_remove_unused_faculty_page_translation_keys.php');
        $this->runMigrationCorrection('2026_08_16_000016_complete_faculty_page_cms_labels.php');
        $this->runMigrationCorrection('2026_08_16_000017_create_department_page_cms_settings.php');
        $this->runMigrationCorrection('2026_08_16_000018_normalize_electrical_power_engineering_department.php');
        $this->runMigrationCorrection('2026_08_16_000019_normalize_architecture_department.php');
        $this->runMigrationCorrection('2026_08_17_000020_unify_vakhitov_staff_profile_departments.php');
        $this->runMigrationCorrection('2026_08_17_000021_add_staff_secondary_departments_apanel_label.php');
        $this->runMigrationCorrection('2026_08_17_000022_normalize_electrical_power_program_pages.php');
        $this->runMigrationCorrection('2026_08_18_000001_create_program_page_cms_settings.php');
        $this->runMigrationCorrection('2026_08_18_000002_localize_linked_program_courses.php');
        $this->runMigrationCorrection('2026_08_18_000003_add_renewable_energy_program_courses.php');
        $this->runMigrationCorrection('2026_08_18_000004_add_light_industry_design_program_courses.php');
        $this->runMigrationCorrection('2026_08_16_000020_normalize_civil_engineering_department.php');
        $this->runMigrationCorrection('2026_08_16_000021_normalize_light_industry_engineering_design_department.php');
        $this->runMigrationCorrection('2026_08_16_000022_normalize_mechanics_engineering_graphics_department.php');
        $this->runMigrationCorrection('2026_08_16_000023_add_mechanical_engineering_program.php');
        $this->runMigrationCorrection('2026_08_18_000005_add_mechanical_engineering_program_courses.php');
        $this->runMigrationCorrection('2026_08_16_000024_normalize_technological_machines_equipment_department.php');
        $this->runMigrationCorrection('2026_08_18_000006_add_technological_machines_program_courses.php');
        $this->runMigrationCorrection('2026_08_17_000001_normalize_technology_department_sections.php');
        $this->runMigrationCorrection('2026_08_17_000002_normalize_oil_gas_refining_staff.php');
        $this->runMigrationCorrection('2026_08_17_000003_normalize_food_technology_service_staff.php');
        $this->runMigrationCorrection('2026_08_17_000004_normalize_chemical_technology_staff.php');
        $this->runMigrationCorrection('2026_08_18_000007_add_chemical_technology_program_courses.php');
        $this->runMigrationCorrection('2026_08_17_000005_normalize_agricultural_products_storage_oil_fat_staff.php');
        $this->runMigrationCorrection('2026_08_18_000008_add_agricultural_products_program_courses.php');
        $this->runMigrationCorrection('2026_08_17_000006_normalize_oil_gas_engineering_staff.php');
        $this->runMigrationCorrection('2026_08_18_000009_add_oil_gas_engineering_program_courses.php');
        $this->runMigrationCorrection('2026_08_17_000007_normalize_metrology_standardization_staff.php');
        $this->runMigrationCorrection('2026_08_19_000001_add_metrology_standardization_program_courses.php');
        $this->runMigrationCorrection('2026_08_15_000008_delete_obsolete_inactive_departments.php');
        $this->runMigrationCorrection('2026_08_15_000009_clean_broken_staff_html_fragments.php');
        $this->runMigrationCorrection('2026_08_15_000010_deduplicate_staff_profiles.php');
        $this->runMigrationCorrection('2026_08_15_000011_finalize_staff_deduplication.php');
        $this->runMigrationCorrection('2026_08_15_000012_delete_inactive_staff_profiles.php');
        $this->runMigrationCorrection('2026_08_16_000001_normalize_irrigation_melioration_master_subjects.php');
        $this->runMigrationCorrection('2026_08_16_000002_normalize_irrigation_melioration_research_staff.php');
        $this->runMigrationCorrection('2026_08_16_000003_localize_irrigation_melioration_research_items.php');
        $this->runMigrationCorrection('2026_08_17_000008_normalize_irrigation_melioration_subject_structure.php');
        $this->runMigrationCorrection('2026_08_19_000002_add_irrigation_melioration_program_courses.php');
        $this->runMigrationCorrection('2026_08_17_000009_normalize_hydrotechnical_structures_pump_stations_department.php');
        $this->runMigrationCorrection('2026_08_19_000003_add_hydrotechnical_program_courses.php');
        $this->runMigrationCorrection('2026_08_16_000004_normalize_agricultural_water_engineering_department.php');
        $this->runMigrationCorrection('2026_08_19_000004_add_agricultural_mechanization_program_courses.php');
        $this->runMigrationCorrection('2026_08_16_000005_localize_agricultural_water_engineering_partners.php');
        $this->runMigrationCorrection('2026_08_17_000010_normalize_agricultural_water_subject_structure.php');
        $this->runMigrationCorrection('2026_08_16_000006_normalize_land_resources_department.php');
        $this->runMigrationCorrection('2026_08_17_000011_normalize_land_resources_subject_structure.php');
        $this->runMigrationCorrection('2026_08_19_000005_add_land_resources_program_courses.php');
        $this->runMigrationCorrection('2026_08_16_000007_normalize_industrial_ecology_hydrogeology_department.php');
        $this->runMigrationCorrection('2026_08_17_000012_normalize_industrial_ecology_subject_structure.php');
        $this->runMigrationCorrection('2026_08_19_000006_add_industrial_ecology_program_courses.php');
        $this->runMigrationCorrection('2026_08_16_000008_normalize_vehicle_engineering_department.php');
        $this->runMigrationCorrection('2026_08_17_000013_normalize_vehicle_engineering_subject_structure.php');
        $this->runMigrationCorrection('2026_08_19_000007_add_vehicle_engineering_program_courses.php');
        $this->runMigrationCorrection('2026_08_16_000009_normalize_technological_processes_automation_department.php');
        $this->runMigrationCorrection('2026_08_17_000014_normalize_technological_processes_subject_structure.php');
        $this->runMigrationCorrection('2026_08_16_000010_normalize_ict_department_staff.php');
        $this->runMigrationCorrection('2026_08_17_000015_normalize_ict_department_sections.php');
        $this->runMigrationCorrection('2026_08_16_000011_normalize_economics_management_staff.php');
        $this->runMigrationCorrection('2026_08_17_000016_normalize_economics_management_sections.php');
        $this->runMigrationCorrection('2026_08_19_000008_add_automation_it_economics_program_courses.php');
        $this->runMigrationCorrection('2026_08_16_000012_normalize_social_sciences_physical_culture_department.php');
        $this->runMigrationCorrection('2026_08_17_000017_normalize_social_sciences_sections.php');
        $this->runMigrationCorrection('2026_08_16_000013_normalize_exact_sciences_department.php');
        $this->runMigrationCorrection('2026_08_17_000018_normalize_exact_sciences_sections.php');
        $this->runMigrationCorrection('2026_08_16_000014_normalize_uzbek_foreign_languages_department.php');
        $this->runMigrationCorrection('2026_08_17_000019_normalize_uzbek_foreign_languages_sections.php');
        $this->runMigrationCorrection('2026_08_16_000015_move_home_program_labels_to_home_cms.php');

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    private function runMigrationCorrection(string $file): void
    {
        $path = database_path('migrations/'.$file);

        if (! is_file($path)) {
            return;
        }

        $migration = require $path;

        if (is_object($migration) && method_exists($migration, 'up')) {
            $migration->up();
        }
    }

    private function syncServiceAndDigitalization(): void
    {
        $facultyId = $this->facultyId('faculty-of-service-and-digitalization');

        if (! $facultyId) {
            return;
        }

        foreach ($this->serviceDepartments() as $slug => $department) {
            DB::table('departments')->where('slug', $slug)->update([
                'faculty_id' => $facultyId,
                'head_name' => $department['head_name'],
                'email' => $department['email'],
                'phone' => $department['phone'],
                'reception_time' => $department['reception_time'],
                'sort_order' => $department['sort_order'],
                'is_active' => true,
                'updated_at' => now(),
            ]);

            $this->ensureDepartmentHeadProfile($slug, $facultyId, $department);
        }

        DB::table('departments')
            ->where('faculty_id', $facultyId)
            ->whereNotIn('slug', array_keys($this->serviceDepartments()))
            ->update(['is_active' => false, 'updated_at' => now()]);

        $this->syncServiceMenu();
    }

    private function syncEngineering(): void
    {
        $facultyId = $this->facultyId('faculty-of-engineering');

        if (! $facultyId) {
            return;
        }

        DB::table('faculty_translations')->updateOrInsert(
            ['faculty_id' => $facultyId, 'locale' => 'en'],
            [
                'name' => 'Faculty of Engineering',
                'short_name' => 'Faculty of Engineering',
                'description' => $this->engineeringDescription(),
                'content_sections' => json_encode($this->engineeringFacultySections(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'meta_title' => 'Faculty of Engineering',
                'meta_description' => Str::limit($this->engineeringDescription(), 240, ''),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        foreach ($this->engineeringDepartments() as $slug => $department) {
            DB::table('departments')->where('slug', $slug)->update([
                'faculty_id' => $facultyId,
                'head_name' => $department['head_name'],
                'email' => $department['email'],
                'phone' => $department['phone'],
                'reception_time' => $department['reception_time'],
                'sort_order' => $department['sort_order'],
                'is_active' => true,
                'updated_at' => now(),
            ]);

            $this->ensureDepartmentHeadProfile($slug, $facultyId, $department);
        }

        DB::table('departments')
            ->where('faculty_id', $facultyId)
            ->whereNotIn('slug', array_keys($this->engineeringDepartments()))
            ->update(['is_active' => false, 'updated_at' => now()]);

        foreach ($this->engineeringLeadership() as $leader) {
            $this->ensureStaffProfile($leader['slug'], $facultyId, null, $leader);
        }

        DB::table('programs')
            ->where('faculty_id', $facultyId)
            ->whereNull('official_code')
            ->update(['is_active' => false, 'updated_at' => now()]);

        foreach ($this->engineeringPrograms() as $index => $program) {
            $departmentId = DB::table('departments')->where('slug', $program['department_slug'])->value('id');

            if (! $departmentId) {
                continue;
            }

            $programId = DB::table('programs')->where('slug', $program['slug'])->value('id');
            $payload = [
                'faculty_id' => $facultyId,
                'department_id' => $departmentId,
                'slug' => $program['slug'],
                'code' => $program['slug'],
                'official_code' => $program['code'],
                'track' => $program['track'],
                'degree' => 'bachelor',
                'duration_years' => 4,
                'study_mode' => 'full_time',
                'language_of_study' => 'en',
                'tuition_fee' => 0,
                'currency' => 'UZS',
                'image' => null,
                'is_active' => true,
                'sort_order' => $index + 1,
                'updated_at' => now(),
            ];

            if ($programId) {
                DB::table('programs')->where('id', $programId)->update($payload);
            } else {
                $programId = DB::table('programs')->insertGetId($payload + ['created_at' => now()]);
            }

            foreach ($this->locales() as $locale) {
                DB::table('program_translations')->updateOrInsert(
                    ['program_id' => $programId, 'locale' => $locale],
                    [
                        'name' => $program['name'],
                        'description' => $program['description'],
                        'requirements' => 'Secondary education certificate or equivalent, application documents, and admission requirements approved by the university.',
                        'documents' => 'Passport, education certificate, transcript, photo, and required application documents.',
                        'curriculum_summary' => $program['curriculum_summary'],
                        'career_opportunities' => $program['career_opportunities'],
                        'meta_title' => $program['name'],
                        'meta_description' => Str::limit($program['description'], 240, ''),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    private function syncServiceMenu(): void
    {
        $mainMenuId = DB::table('menus')
            ->whereIn('key', ['main_header', 'main'])
            ->where('location', 'header')
            ->value('id');
        $parentId = DB::table('menu_items')->where('url', '/faculty/faculty-of-service-and-digitalization')->value('id');

        if (! $mainMenuId || ! $parentId) {
            return;
        }

        DB::table('menu_items')
            ->where('menu_id', $mainMenuId)
            ->where('parent_id', $parentId)
            ->whereNotIn('url', array_map(fn ($slug) => '/department/'.$slug, array_keys($this->serviceDepartments())))
            ->update(['is_active' => false, 'updated_at' => now()]);

        foreach ($this->serviceDepartments() as $slug => $department) {
            $this->ensureMenuItem(
                (int) $mainMenuId,
                (int) $parentId,
                '/department/'.$slug,
                $department['sort_order'],
                $department['name']
            );
        }
    }

    private function ensureMenuItem(int $menuId, int $parentId, string $url, int $sortOrder, string $label): void
    {
        $itemId = DB::table('menu_items')->where('url', $url)->value('id');
        $payload = [
            'menu_id' => $menuId,
            'parent_id' => $parentId,
            'route_name' => 'link',
            'url' => $url,
            'sort_order' => $sortOrder,
            'is_active' => true,
            'updated_at' => now(),
        ];

        if ($itemId) {
            DB::table('menu_items')->where('id', $itemId)->update($payload);
        } else {
            $itemId = DB::table('menu_items')->insertGetId($payload + ['created_at' => now()]);
        }

        foreach ($this->locales() as $locale) {
            DB::table('menu_item_translations')->updateOrInsert(
                ['menu_item_id' => $itemId, 'locale' => $locale],
                ['label' => $label, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    private function ensureDepartmentHeadProfile(string $departmentSlug, int $facultyId, array $department): void
    {
        $departmentId = DB::table('departments')->where('slug', $departmentSlug)->value('id');

        if (! $departmentId || empty($department['profile_slug'])) {
            return;
        }

        $this->ensureStaffProfile($department['profile_slug'], $facultyId, (int) $departmentId, [
            'name' => $department['head_name'],
            'position' => 'Head of Department',
            'phone' => $department['phone'],
            'email' => $department['email'],
            'office' => $department['reception_time'],
            'photo' => $department['photo'] ?? null,
            'sort_order' => 10,
        ]);
    }

    private function ensureStaffProfile(string $slug, int $facultyId, ?int $departmentId, array $person): void
    {
        $profileId = DB::table('staff_profiles')->where('slug', $slug)->value('id');
        $payload = [
            'faculty_id' => $facultyId,
            'department_id' => $departmentId,
            'photo' => $person['photo'] ?? null,
            'email' => $person['email'],
            'phone' => $person['phone'],
            'sort_order' => $person['sort_order'] ?? 10,
            'is_active' => true,
            'updated_at' => now(),
        ];

        if ($profileId) {
            DB::table('staff_profiles')->where('id', $profileId)->update($payload);
        } else {
            $profileId = DB::table('staff_profiles')->insertGetId($payload + [
                'slug' => $slug,
                'created_at' => now(),
            ]);
        }

        foreach ($this->locales() as $locale) {
            DB::table('staff_profile_translations')->updateOrInsert(
                ['staff_profile_id' => $profileId, 'locale' => $locale],
                [
                    'full_name' => $locale === 'ar' ? $this->arabicPersonName($person['name']) : $person['name'],
                    'position' => $person['position'],
                    'bio' => $person['position'],
                    'office' => $person['office'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function arabicPersonName(string $name): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', str_replace(['`', '’', 'ʻ'], "'", $name)));
        if ($clean === '' || preg_match('/\p{Arabic}/u', $clean) === 1) {
            return $clean;
        }

        $exact = $this->exactArabicNameMap()[$clean] ?? null;
        if ($exact) {
            return $exact;
        }

        $roles = [
            'to be entered' => 'سيتم الإدخال',
            'senior lecturer' => 'محاضر أول',
            'senior teacher' => 'محاضر أول',
            'lecturer trainee' => 'محاضر متدرب',
            'teacher trainee' => 'مدرس متدرب',
            'doctoral student' => 'باحث دكتوراه',
            'basic doctoral student' => 'باحث دكتوراه أساسي',
        ];
        $lower = Str::lower($clean);
        if (isset($roles[$lower])) {
            return $roles[$lower];
        }

        $prefix = '';
        $titles = [
            '/^assoc\.\s*prof\.\s*/i' => 'أ.م. ',
            '/^prof\.\s*/i' => 'أ. ',
            '/^dr\.\s*/i' => 'د. ',
            '/^phd,?\s*/i' => 'د. ',
        ];

        foreach ($titles as $pattern => $replacement) {
            if (preg_match($pattern, $clean)) {
                $prefix = $replacement;
                $clean = preg_replace($pattern, '', $clean);
                break;
            }
        }

        $parts = preg_split('/\s+/', trim($clean)) ?: [];
        $parts = array_values(array_filter(array_map(fn (string $part) => $this->arabicWord($part), $parts)));

        return trim($prefix.implode(' ', $parts)) ?: $name;
    }

    private function exactArabicNameMap(): array
    {
        return [
            'Dr. Khojiyev Aziz Kholmurodovich' => 'د. خوجييف عزيز خولمورودوفيتش',
            'Xojiyev Aziz Xolmurodovich' => 'خوجييف عزيز خولمورودوفيتش',
            'Rustamov Bobir Ismatovich' => 'رستاموف بوبير إسماتوفيتش',
            'Ashurov Asrorjon Komilovich' => 'أشوروف أسرورجون كوميلوفيتش',
            'Latipov Saidmurod Tuygunovich' => 'لاتيبوف سعيد مراد تويغونوفيتش',
            'Mirzayev Shamsiddin Rajabovich' => 'ميرزاييف شمس الدين رجبوفيتش',
            "Tojiyev In'omjon Ilhomovich" => 'توجييف إنعام جون إلهوموفيتش',
            'Qazoqov Farxod Farmonovich' => 'قازوقوف فرخود فرمانوفيتش',
            'Xabibov Faxriddin Yusupovich' => 'خبيبوف فخر الدين يوسفوفيتش',
            'O‘rinov Uyg‘un Abdullayevich' => 'أورينوف أوغون عبد اللهيفيتش',
            "O'rinov Uyg'un Abdullayevich" => 'أورينوف أوغون عبد اللهيفيتش',
        ];
    }

    private function arabicWord(string $word): string
    {
        $word = trim($word, " \t\n\r\0\x0B.,;:()[]{}");
        $special = [
            "o'g'li" => 'أوغلي',
            'ogli' => 'أوغلي',
            'oglu' => 'أوغلي',
            'qizi' => 'قيزي',
            'kizi' => 'قيزي',
            'dsc' => 'دكتور علوم',
            'phd' => 'دكتوراه',
        ];

        $lower = Str::lower($word);
        if (isset($special[$lower])) {
            return $special[$lower];
        }

        $map = [
            'yo' => 'يو', 'yu' => 'يو', 'ya' => 'يا', 'ye' => 'ي', 'iy' => 'ي',
            'kh' => 'خ', 'x' => 'خ', 'sh' => 'ش', 'ch' => 'تش', 'ts' => 'تس',
            'zh' => 'ج', "g'" => 'غ', 'g‘' => 'غ', "o'" => 'أو', 'o‘' => 'أو',
            'a' => 'ا', 'b' => 'ب', 'c' => 'ك', 'd' => 'د', 'e' => 'ي',
            'f' => 'ف', 'g' => 'غ', 'h' => 'ه', 'i' => 'ي', 'j' => 'ج',
            'k' => 'ك', 'l' => 'ل', 'm' => 'م', 'n' => 'ن', 'o' => 'و',
            'p' => 'ب', 'q' => 'ق', 'r' => 'ر', 's' => 'س', 't' => 'ت',
            'u' => 'و', 'v' => 'ف', 'w' => 'و', 'y' => 'ي', 'z' => 'ز',
            "'" => '',
        ];
        uksort($map, fn ($a, $b) => strlen($b) <=> strlen($a));

        return strtr($lower, $map);
    }

    private function serviceDepartments(): array
    {
        return [
            'technological-processes-production-automation' => [
                'name' => 'Department of Technological Processes and Production Automation',
                'head_name' => 'Qobilov Hasan Xalilovich',
                'email' => 'h.qobilov@mail.ru',
                'phone' => '+998 91 409 66 18',
                'reception_time' => 'Daily 14:00-16:00',
                'sort_order' => 10,
                'profile_slug' => 'technological-processes-production-automation-qobilov-hasan-xalilovich',
                'photo' => 'cms/staff/Qobilov Hasan Xalilovich.jpg',
            ],
            'information-and-communication-technologies' => [
                'name' => 'Department of Information and Communication Technologies',
                'head_name' => 'Atoyev Fazliddin Sayfiddinovich',
                'email' => null,
                'phone' => '+998 99 380 81 88',
                'reception_time' => 'Monday-Friday 14:00-16:00',
                'sort_order' => 20,
                'profile_slug' => 'information-and-communication-technologies-atoyev-fazliddin-sayfiddinovich',
                'photo' => 'cms/staff/Atoyev Fazliddin Sayfiddinovich.jpg',
            ],
            'economics-and-management' => [
                'name' => 'Department of Economics and Management',
                'head_name' => 'Boboyev Akmal Choriyevich',
                'email' => 'boboyevakmal1974@gmail.com',
                'phone' => '+998 97 306 31 32',
                'reception_time' => 'Monday-Friday 14:00-16:00',
                'sort_order' => 30,
                'profile_slug' => 'economics-and-management-boboyev-akmal-choriyevich',
                'photo' => 'cms/staff/Boboyev Akmal Choriyevich.jpg',
            ],
            'social-sciences-physical-culture' => [
                'name' => 'Department of Social Sciences and Physical Culture',
                'head_name' => 'Murodov Sanjar Aslonovich',
                'email' => null,
                'phone' => '+998 91 416 75 10',
                'reception_time' => 'Daily 14:00-16:00',
                'sort_order' => 40,
                'profile_slug' => 'social-sciences-physical-culture-murodov-sanjar-aslonovich',
                'photo' => 'cms/staff/Murodov Sanjar Aslonovich.jpg',
            ],
            'exact-sciences' => [
                'name' => 'Department of Exact Sciences',
                'head_name' => 'Kasimova Guzal Karimovna',
                'email' => null,
                'phone' => '+998 94 490 22 90',
                'reception_time' => 'Monday-Friday 14:00-16:00',
                'sort_order' => 50,
                'profile_slug' => 'exact-sciences-kasimova-guzal-karimovna',
                'photo' => 'cms/staff/Kasimova Guzal Karimovna.jpg',
            ],
            'uzbek-foreign-languages' => [
                'name' => 'Department of Uzbek and Foreign Languages',
                'head_name' => 'Yusupova Shokhida Batirovna',
                'email' => null,
                'phone' => '+998 93 623 74 72',
                'reception_time' => 'Monday-Friday 14:00-16:00',
                'sort_order' => 60,
                'profile_slug' => 'uzbek-foreign-languages-yusupova-shokhida-batirovna',
                'photo' => 'cms/staff/Yusupova Shokhida Batirovna.jpg',
            ],
        ];
    }

    private function engineeringDepartments(): array
    {
        return [
            'electrical-power-engineering' => ['name' => 'Electrical and Power Engineering', 'head_name' => 'Latipov Saidmurod Tuygunovich', 'email' => 'stlatipov@gmail.com', 'phone' => '+998 91 979 88 22', 'reception_time' => 'Tuesday-Thursday 10:00-13:00', 'sort_order' => 10, 'profile_slug' => 'electrical-power-engineering-latipov-saidmurod-tuygunovich', 'photo' => 'cms/staff/latipov-saidmurod-tuygunovich.jpg'],
            'architecture' => ['name' => 'Architecture', 'head_name' => 'Mirzayev Shamsiddin Rajabovich', 'email' => 'mirzaev.shamsiddin@mail.ru', 'phone' => '+998 91 014 02 59', 'reception_time' => 'Monday-Friday 14:00-16:00', 'sort_order' => 20, 'profile_slug' => 'architecture-mirzaev-shamsiddin-rajabovich', 'photo' => 'cms/staff/mirzaev-shamsiddin-rajabovich.jpg'],
            'civil-engineering' => ['name' => 'Civil Engineering', 'head_name' => "Tojiyev In'omjon Ilhomovich", 'email' => 'arminom@mail.ru', 'phone' => '+998 91 444 87 03', 'reception_time' => 'Monday-Friday 14:00-16:00', 'sort_order' => 30, 'profile_slug' => 'civil-engineering-inomjon-ilhomovich-tojiyev', 'photo' => 'cms/staff/inomjon-ilhomovich-tojiyev.png'],
            'light-industry-engineering-and-design' => ['name' => 'Light Industry Engineering and Design', 'head_name' => 'Qazoqov Farxod Farmonovich', 'email' => null, 'phone' => '+998 91 647 65 82', 'reception_time' => 'Monday-Friday 14:00-16:00', 'sort_order' => 40, 'profile_slug' => 'light-industry-engineering-and-design-farhod-farmonovich-qazoqov', 'photo' => 'cms/staff/farhod-farmonovich-qazoqov.jpg'],
            'mechanics-engineering-graphics' => ['name' => 'Mechanics and Engineering Graphics', 'head_name' => 'Xabibov Faxriddin Yusupovich', 'email' => 'faxrilo@mail.ru', 'phone' => '+998 93 379 65 00', 'reception_time' => 'Monday-Friday 14:00-16:00', 'sort_order' => 50, 'profile_slug' => 'mechanics-engineering-graphics-fakhriddin-yusupovich-khabibov', 'photo' => 'cms/staff/fakhriddin-yusupovich-khabibov.jpg'],
            'technological-machines-equipment' => ['name' => 'Technological Machines and Equipment', 'head_name' => 'O‘rinov Uyg‘un Abdullayevich', 'email' => null, 'phone' => '+998 90 744 18 22', 'reception_time' => 'Monday-Friday 14:00-16:00', 'sort_order' => 60, 'profile_slug' => 'technological-machines-equipment-uygun-abdullayevich-orinov', 'photo' => 'cms/staff/uygun-abdullayevich-orinov.jpg'],
        ];
    }

    private function engineeringLeadership(): array
    {
        return [
            ['slug' => 'faculty-of-engineering-dean-xojiyev-aziz-kholmurodovich', 'name' => 'Dr. Khojiyev Aziz Kholmurodovich', 'position' => 'Dean', 'phone' => '+998 (90) 744 01 79', 'email' => 'azizhojiyev1979y@mail.ru', 'office' => 'Daily 14:00-16:00', 'photo' => 'cms/staff/khojiyev-aziz-kholmurodovich.jpg', 'sort_order' => 10],
            ['slug' => 'faculty-of-engineering-academic-rustamov-bobir-ismatovich', 'name' => 'Rustamov Bobir Ismatovich', 'position' => 'Deputy Dean for Academic Affairs', 'phone' => '+998 (99) 704 79 72', 'email' => 'bobir_rustamov@bk.ru', 'office' => 'Daily 14:00-16:00', 'photo' => 'cms/staff/rustamov-bobir-ismatovich.jpg', 'sort_order' => 20],
            ['slug' => 'faculty-of-engineering-youth-ashurov-asrorjon-komilovich', 'name' => 'Ashurov Asrorjon Komilovich', 'position' => 'Deputy Dean for Youth Affairs', 'phone' => '(+998 97) 488-28-22', 'email' => 'a.asrorjon83@mail.ru', 'office' => 'Daily 14:00-16:00', 'photo' => 'cms/staff/ashurov-asrorjon-komilovich.jpg', 'sort_order' => 30],
        ];
    }

    private function engineeringPrograms(): array
    {
        return [
            $this->program('60210400', 'Design: footwear and accessories design', 'design-footwear-and-accessories-design-60210400', 'Footwear and Accessories Design', 'light-industry-engineering-and-design'),
            $this->program('60210400', 'Design: Clothing and Textile Design', 'design-clothing-and-textile-design-60210400', 'Clothing and Textile Design', 'light-industry-engineering-and-design'),
            $this->program('60210400', 'Design: textile and light industry design', 'design-textile-and-light-industry-design-60210400', 'Textile and Light Industry Design', 'light-industry-engineering-and-design'),
            $this->program('60710400', 'Energy Engineering', 'energy-engineering-60710400', null, 'electrical-power-engineering'),
            $this->program('60710500', 'Electrical Engineering', 'electrical-engineering-60710500', null, 'electrical-power-engineering'),
            $this->program('60711800', 'Environmental Engineering', 'environmental-engineering-60711800', null, 'electrical-power-engineering'),
            $this->program('60712000', 'Renewable Energy Sources', 'renewable-energy-sources-60712000', null, 'electrical-power-engineering'),
            $this->program('60712300', 'Mechanical Engineering', 'mechanical-engineering-60712300', null, 'mechanics-engineering-graphics'),
            $this->program('60720400', 'Technological Machines and Equipment', 'technological-machines-and-equipment-60720400', null, 'technological-machines-equipment'),
            $this->program('60720700', 'Light Industry Engineering', 'light-industry-engineering-60720700', null, 'light-industry-engineering-and-design'),
            $this->program('60721800', 'Manufacturing Engineering', 'manufacturing-engineering-60721800', null, 'technological-machines-equipment'),
            $this->program('60730100', 'Architecture', 'architecture-60730100', null, 'architecture'),
            $this->program('60730300', 'Civil Engineering', 'civil-engineering-60730300', null, 'civil-engineering'),
            $this->program('60730400', 'Construction and Operation of Engineering Communications', 'construction-and-operation-of-engineering-communications-60730400', null, 'civil-engineering'),
            $this->program('60730500', 'Road Engineering', 'road-engineering-60730500', null, 'civil-engineering'),
            $this->program('60730800', 'Reconstruction and Restoration of Architectural Monuments', 'reconstruction-and-restoration-of-architectural-monuments-60730800', null, 'architecture'),
            $this->program('60730900', 'Urban Construction and Planning', 'urban-construction-and-planning-60730900', null, 'architecture'),
            $this->program('60731100', 'Production of Construction Materials, Products and Structures', 'production-of-construction-materials-products-and-structures-60731100', null, 'civil-engineering'),
        ];
    }

    private function engineeringDescription(): string
    {
        return 'The Faculty of Engineering is an academic division that prepares highly qualified specialists in core engineering fields. Students gain theoretical knowledge and practical skills in modern technologies, design, manufacturing processes, construction, energy, and innovative engineering solutions.';
    }

    private function engineeringFacultySections(): array
    {
        return [
            ['key' => 'overview', 'title' => 'About the Faculty', 'items' => ['The Faculty of Engineering prepares competent professionals for industry, construction, energy, architecture, manufacturing, and other technical sectors.', 'Academic training combines theory, laboratory work, design practice, production processes, and innovative solutions.']],
            ['key' => 'departments', 'title' => 'Departments', 'items' => array_map(fn ($department) => $department['name'], $this->engineeringDepartments())],
            ['key' => 'leadership', 'title' => 'Faculty Leadership', 'items' => array_map(fn ($leader) => $leader['name'].' - '.$leader['position'], $this->engineeringLeadership())],
        ];
    }

    private function program(string $code, string $name, string $slug, ?string $track, string $departmentSlug): array
    {
        return [
            'code' => $code,
            'name' => $name,
            'slug' => $slug,
            'track' => $track,
            'department_slug' => $departmentSlug,
            'description' => $name.' is a four-year bachelor program in the Faculty of Engineering. It combines theoretical study, practical training, laboratory work, design projects, and industry-oriented learning.',
            'curriculum_summary' => 'Duration of study: 4 years. Scholarship fields are currently recorded as not specified. Tuition is stored as 0.00 UZS until official fee values are provided.',
            'career_opportunities' => 'Graduates can work in engineering companies, production enterprises, design organizations, construction firms, energy and industrial sectors, public institutions, and research or innovation projects.',
        ];
    }

    private function facultyId(string $slug): ?int
    {
        $id = DB::table('faculties')->where('slug', $slug)->value('id');

        return $id ? (int) $id : null;
    }

    private function locales(): array
    {
        return DB::table('locales')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter()
            ->values()
            ->all() ?: ['en', 'uz', 'ru', 'ar'];
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SqlCmsSnapshotSeeder extends Seeder
{
    /**
     * Import CMS content extracted from the legacy SQL dump.
     *
     * The import uses insertOrIgnore so rerunning the seeder does not overwrite
     * content edited later from apanel and does not duplicate rows with matching
     * primary or unique keys.
     */
    public function run(): void
    {
        $path = database_path('data/sql_cms_snapshot.json');

        if (! is_file($path)) {
            $this->command?->warn('SQL CMS snapshot file was not found.');

            return;
        }

        $snapshot = json_decode((string) file_get_contents($path), true);
        $tables = $snapshot['tables'] ?? [];

        foreach ($this->tableOrder() as $table) {
            $rows = $tables[$table] ?? [];

            if ($rows === []) {
                continue;
            }

            $inserted = 0;

            foreach (array_chunk($rows, 10) as $chunk) {
                $inserted += DB::table($table)->insertOrIgnore($chunk);
            }

            $this->command?->info(sprintf(
                'Seeded %s: %d inserted, %d skipped.',
                $table,
                $inserted,
                count($rows) - $inserted,
            ));
        }
    }

    /**
     * Parent tables are imported before their translation/child tables.
     *
     * @return list<string>
     */
    private function tableOrder(): array
    {
        return [
            'locales',
            'translation_keys',
            'translation_values',
            'faculties',
            'faculty_translations',
            'departments',
            'department_translations',
            'programs',
            'program_translations',
            'courses',
            'course_translations',
            'program_courses',
            'staff_profiles',
            'staff_profile_translations',
            'menus',
            'menu_items',
            'menu_item_translations',
            'pages',
            'page_translations',
            'page_blocks',
            'page_block_translations',
            'settings',
            'media',
            'services',
            'service_translations',
            'news_event_settings',
            'news_event_setting_translations',
            'news',
            'news_translations',
            'announcement_settings',
            'announcement_setting_translations',
            'announcements',
            'announcement_translations',
            'blog_settings',
            'blog_setting_translations',
            'blogs',
            'blog_translations',
            'video_gallery_settings',
            'video_gallery_setting_translations',
            'videos',
            'video_translations',
            'web_footers',
            'web_footer_translations',
            'green_campus_settings',
            'green_campus_setting_translations',
            'green_campus_stats',
            'green_campus_stat_translations',
            'green_campus_articles',
            'green_campus_article_translations',
            'administration_settings',
            'administration_setting_translations',
            'administration_profiles',
            'administration_profile_translations',
            'contact_pages',
            'contact_page_translations',
            'university_center_settings',
            'university_center_setting_translations',
            'university_centers',
            'university_center_translations',
            'document_requirements',
            'application_countries',
            'application_nationalities',
        ];
    }
}

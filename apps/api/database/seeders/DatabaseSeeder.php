<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LocaleSeeder::class,
            PermissionSeeder::class,
            RoleSeeder::class,
            ApanelUserSeeder::class,
            TranslationKeySeeder::class,
            TranslationValueSeeder::class,
            StudentSystemTranslationSeeder::class,
            MenuSeeder::class,
            PageSeeder::class,
            PageBlockSeeder::class,
            FacultySeeder::class,
            DepartmentSeeder::class,
            ProgramSeeder::class,
            CourseSeeder::class,
            NewsSeeder::class,
            BlogSeeder::class,
            AnnouncementSeeder::class,
            StaffSeeder::class,
            TechnologyFacultyContentSeeder::class,
            ServiceSeeder::class,
            VideoSeeder::class,
            GreenCampusSeeder::class,
            AdministrationSeeder::class,
            SettingSeeder::class,
            WebFooterSeeder::class,
        ]);
    }
}

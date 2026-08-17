<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('departments')
            ->where('slug', 'hydrotechnical-structures-pump-stations')
            ->update([
                'head_name' => 'Axmedov Sharifboy Ro‘ziyevich',
                'email' => null,
                'phone' => '+998 91 444 72 27',
                'reception_time' => 'Monday-Friday 14:00-16:00',
                'updated_at' => now(),
            ]);

        $profileId = DB::table('staff_profiles')
            ->where('slug', 'hydrotechnical-structures-pump-stations-hydraulic-structures-and-pumping-stations')
            ->value('id');

        if ($profileId) {
            DB::table('staff_profiles')
                ->where('id', $profileId)
                ->update([
                    'email' => null,
                    'phone' => '+998 91 444 72 27',
                    'updated_at' => now(),
                ]);

            foreach ($this->profileTranslations() as $locale => $translation) {
                DB::table('staff_profile_translations')->updateOrInsert(
                    ['staff_profile_id' => $profileId, 'locale' => $locale],
                    array_merge($translation, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );
            }
        }

        $departmentId = DB::table('departments')
            ->where('slug', 'hydrotechnical-structures-pump-stations')
            ->value('id');

        if ($departmentId) {
            DB::table('department_translations')->updateOrInsert(
                ['department_id' => $departmentId, 'locale' => 'en'],
                [
                    'name' => 'Department of Hydraulic Structures and Pumping Stations',
                    'short_name' => 'Hydraulic Structures and Pumping Stations',
                    'description' => $this->description(),
                    'content_sections' => json_encode($this->sections(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'meta_title' => 'Department of Hydraulic Structures and Pumping Stations',
                    'meta_description' => $this->description(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('departments')
            ->where('slug', 'hydrotechnical-structures-pump-stations')
            ->update([
                'head_name' => 'Hydraulic structures and pumping stations',
                'phone' => '+998 (91) 444 72 27',
                'reception_time' => 'Dushanba-Juma (14:00-16:00 gacha)',
                'updated_at' => now(),
            ]);
    }

    private function profileTranslations(): array
    {
        return [
            'en' => [
                'full_name' => 'Axmedov Sharifboy Ro‘ziyevich',
                'position' => 'Head of Department',
                'bio' => 'Head of Department',
                'office' => 'Monday-Friday 14:00-16:00',
            ],
            'uz' => [
                'full_name' => 'Axmedov Sharifboy Ro‘ziyevich',
                'position' => 'Kafedra mudiri',
                'bio' => 'Kafedra mudiri',
                'office' => 'Dushanba-Juma 14:00-16:00',
            ],
            'ru' => [
                'full_name' => 'Axmedov Sharifboy Ro‘ziyevich',
                'position' => 'Заведующий кафедрой',
                'bio' => 'Заведующий кафедрой',
                'office' => 'Понедельник-Пятница 14:00-16:00',
            ],
            'ar' => [
                'full_name' => 'Axmedov Sharifboy Ro‘ziyevich',
                'position' => 'رئيس القسم',
                'bio' => 'رئيس القسم',
                'office' => 'الاثنين-الجمعة 14:00-16:00',
            ],
        ];
    }

    private function description(): string
    {
        return 'Hydraulic Structures and Pumping Stations is an important engineering field that ensures the management, distribution, and efficient use of water resources.';
    }

    private function sections(): array
    {
        return [
            [
                'key' => 'overview',
                'title' => 'About the Department',
                'items' => [
                    'Hydraulic Structures and Pumping Stations is an important engineering field that ensures the management, distribution, and efficient use of water resources.',
                    'This field focuses on the design, construction, and operation of reservoirs, canals, dams, hydraulic structures, and modern pumping stations.',
                    'It contributes to the development of water management, energy, and agriculture by preparing highly qualified specialists.',
                ],
            ],
            [
                'key' => 'history',
                'title' => 'Kafedra tarixi',
                'items' => [],
            ],
            [
                'key' => 'research',
                'title' => 'Scientific Activity',
                'items' => [],
            ],
            [
                'key' => 'prepared_specialists',
                'title' => 'Specialists Trained by the Department',
                'items' => [],
            ],
            [
                'key' => 'cooperation',
                'title' => 'International Cooperation',
                'items' => [],
            ],
        ];
    }
};

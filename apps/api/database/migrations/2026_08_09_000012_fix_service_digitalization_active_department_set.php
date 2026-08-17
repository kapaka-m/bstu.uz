<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $activeDepartments = [
        'technological-processes-production-automation' => 10,
        'information-and-communication-technologies' => 20,
        'economics-and-management' => 30,
        'social-sciences-physical-culture' => 40,
        'exact-sciences' => 50,
        'uzbek-foreign-languages' => 60,
    ];

    private array $inactiveDepartments = [
        'artificial-intelligence-digitalization',
        'information-communication-technologies',
        'economics-management',
    ];

    public function up(): void
    {
        foreach ($this->activeDepartments as $slug => $sortOrder) {
            DB::table('departments')
                ->where('slug', $slug)
                ->update([
                    'is_active' => true,
                    'sort_order' => $sortOrder,
                    'updated_at' => now(),
                ]);
        }

        DB::table('departments')
            ->whereIn('slug', $this->inactiveDepartments)
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);

        DB::table('staff_profiles')
            ->whereIn('department_id', DB::table('departments')->whereIn('slug', $this->inactiveDepartments)->pluck('id'))
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);

        DB::table('menu_items')
            ->where('url', '/department/artificial-intelligence-digitalization')
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);

        foreach ($this->headProfiles() as $head) {
            $departmentId = DB::table('departments')->where('slug', $head['department_slug'])->value('id');

            if (! $departmentId) {
                continue;
            }

            DB::table('departments')
                ->where('id', $departmentId)
                ->update([
                    'head_name' => $head['name'],
                    'email' => $head['email'],
                    'phone' => $head['phone'],
                    'reception_time' => $head['office'],
                    'updated_at' => now(),
                ]);

            $profileId = DB::table('staff_profiles')->where('slug', $head['profile_slug'])->value('id');

            if (! $profileId) {
                $profileId = DB::table('staff_profiles')->insertGetId([
                    'slug' => $head['profile_slug'],
                    'department_id' => $departmentId,
                    'faculty_id' => null,
                    'photo' => $head['photo'],
                    'email' => $head['email'],
                    'phone' => $head['phone'],
                    'sort_order' => 0,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('staff_profiles')
                    ->where('id', $profileId)
                    ->update([
                        'department_id' => $departmentId,
                        'photo' => $head['photo'],
                        'email' => $head['email'],
                        'phone' => $head['phone'],
                        'is_active' => true,
                        'updated_at' => now(),
                    ]);
            }

            foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
                DB::table('staff_profile_translations')->updateOrInsert(
                    ['staff_profile_id' => $profileId, 'locale' => $locale],
                    [
                        'full_name' => $head['name'],
                        'position' => $locale === 'en' ? 'Head of Department' : ($locale === 'ar' ? 'رئيس القسم' : ($locale === 'ru' ? 'Заведующий кафедрой' : 'Kafedra mudiri')),
                        'bio' => $locale === 'en' ? 'Head of Department' : ($locale === 'ar' ? 'رئيس القسم' : ($locale === 'ru' ? 'Заведующий кафедрой' : 'Kafedra mudiri')),
                        'office' => $head['office'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        DB::table('departments')
            ->whereIn('slug', array_keys($this->activeDepartments))
            ->update([
                'updated_at' => now(),
            ]);
    }

    private function headProfiles(): array
    {
        return [
            [
                'department_slug' => 'information-and-communication-technologies',
                'profile_slug' => 'information-and-communication-technologies-atoyev-fazliddin-sayfiddinovich',
                'name' => 'Atoyev Fazliddin Sayfiddinovich',
                'email' => null,
                'phone' => '+998 99 380 81 88',
                'office' => 'Monday-Friday 14:00-16:00',
                'photo' => 'cms/staff/Atoyev Fazliddin Sayfiddinovich.jpg',
            ],
            [
                'department_slug' => 'economics-and-management',
                'profile_slug' => 'economics-and-management-boboyev-akmal-choriyevich',
                'name' => 'Boboyev Akmal Choriyevich',
                'email' => 'boboyevakmal1974@gmail.com',
                'phone' => '+998 97 306-31-32',
                'office' => 'Monday-Friday 14:00-16:00',
                'photo' => 'cms/staff/Boboyev Akmal Choriyevich.jpg',
            ],
            [
                'department_slug' => 'uzbek-foreign-languages',
                'profile_slug' => 'uzbek-foreign-languages-yusupova-shoxida-batirovna',
                'name' => 'Yusupova Shokhida Batirovna',
                'email' => null,
                'phone' => '+998 93 623 74 72',
                'office' => 'Monday-Friday 14:00-16:00',
                'photo' => 'cms/staff/Yusupova Shokhida Batirovna.jpg',
            ],
        ];
    }
};

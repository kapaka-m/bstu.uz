<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $departmentId = DB::table('departments')->where('slug', 'metrology-standardization-quality-control')->value('id');

        if (! $departmentId) {
            return;
        }

        DB::transaction(function () use ($departmentId) {
            $this->deleteBrokenStaff($departmentId);
            $this->renameStaffSlugs($departmentId);
            $this->normalizeStaff($departmentId);
            $this->normalizeContentSections($departmentId);
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function deleteBrokenStaff(int $departmentId): void
    {
        $ids = DB::table('staff_profiles')
            ->where('department_id', $departmentId)
            ->whereIn('slug', [
                'metrology-standardization-quality-control-tairov-bakhtiyor-bobokulovich',
                'metrology-standardization-quality-control-assistant-khaidarov',
                'metrology-standardization-quality-control-shukhrat-khikmatullayevich',
                'metrology-standardization-quality-control-0-dr-zebo-a-xamrayeva',
                'metrology-standardization-quality-control-1-sobir-j-xalilov',
                'metrology-standardization-quality-control-2-laylo-r-sharipova',
            ])
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        DB::table('staff_profile_translations')->whereIn('staff_profile_id', $ids)->delete();
        DB::table('staff_profiles')->whereIn('id', $ids)->delete();
    }

    private function renameStaffSlugs(int $departmentId): void
    {
        $this->renameStaffSlug(
            $departmentId,
            'metrology-standardization-quality-control-senior-teacher',
            'metrology-standardization-quality-control-boltaeva-zulfiya-zarifovna',
        );

        $this->renameStaffSlug(
            $departmentId,
            'metrology-standardization-quality-control-phd-senior-lecturer',
            'metrology-standardization-quality-control-sayidakhmedov-ravshan-rajabovich',
        );
    }

    private function renameStaffSlug(int $departmentId, string $oldSlug, string $newSlug): void
    {
        $oldId = DB::table('staff_profiles')
            ->where('department_id', $departmentId)
            ->where('slug', $oldSlug)
            ->value('id');

        if (! $oldId) {
            return;
        }

        $existingId = DB::table('staff_profiles')
            ->where('department_id', $departmentId)
            ->where('slug', $newSlug)
            ->value('id');

        if ($existingId && $existingId !== $oldId) {
            DB::table('staff_profile_translations')->where('staff_profile_id', $oldId)->delete();
            DB::table('staff_profiles')->where('id', $oldId)->delete();

            return;
        }

        DB::table('staff_profiles')->where('id', $oldId)->update([
            'slug' => $newSlug,
            'updated_at' => now(),
        ]);
    }

    private function normalizeStaff(int $departmentId): void
    {
        foreach ($this->staff() as $index => $staff) {
            $profileId = DB::table('staff_profiles')
                ->where('department_id', $departmentId)
                ->where('slug', $staff['slug'])
                ->value('id');

            if (! $profileId) {
                continue;
            }

            DB::table('staff_profiles')->where('id', $profileId)->update([
                'sort_order' => ($index + 1) * 10,
                'is_active' => true,
                'updated_at' => now(),
            ]);

            foreach ($staff['translations'] as $locale => $translation) {
                DB::table('staff_profile_translations')->updateOrInsert(
                    ['staff_profile_id' => $profileId, 'locale' => $locale],
                    [
                        'full_name' => $translation['name'],
                        'position' => $translation['position'],
                        'bio' => $translation['name'].' serves as '.$translation['position'].' in Metrology and Standardization, contributing to teaching, laboratory practice, quality control, and academic development.',
                        'office' => 'Daily 14:00-16:00',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }
        }
    }

    private function normalizeContentSections(int $departmentId): void
    {
        $preparedSpecialists = [
            'en' => '60710800 - Metrology and Standardization',
            'uz' => '60710800 - Metrologiya va standartlashtirish',
            'ru' => '60710800 - Метрология и стандартизация',
            'ar' => '60710800 - المترولوجيا والتقييس',
        ];

        foreach ($preparedSpecialists as $locale => $specialist) {
            $translation = DB::table('department_translations')
                ->where('department_id', $departmentId)
                ->where('locale', $locale)
                ->first();

            if (! $translation) {
                continue;
            }

            $sections = json_decode($translation->content_sections ?? '[]', true);

            if (! is_array($sections)) {
                continue;
            }

            foreach ($sections as &$section) {
                if (($section['key'] ?? null) === 'prepared_specialists') {
                    $section['items'] = [$specialist];
                }
            }
            unset($section);

            DB::table('department_translations')
                ->where('id', $translation->id)
                ->update([
                    'content_sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => now(),
                ]);
        }
    }

    private function staff(): array
    {
        return [
            $this->staffMember('metrology-standardization-quality-control-kafedra-mudiri', 'Tairov Bakhtiyor Boboqulovich', 'Head of Department', 'تايروف بختيار بوبوقولوفيتش', 'رئيس القسم', 'Таиров Бахтиёр Бобокулович', 'Заведующий кафедрой'),
            $this->staffMember('metrology-standardization-quality-control-qurbanov-abdiraxim-axmedovich', 'Qurbanov Abdiraxim Axmedovich', 'Professor', 'قربانوف عبد الرحيم أحمدوفيتش', 'أستاذ', 'Курбанов Абдирахим Ахмедович', 'Профессор'),
            $this->staffMember('metrology-standardization-quality-control-avliyakulov-nadir-nizomovich', 'Avliyakulov Nadir Nizomovich', 'Candidate of Technical Sciences, Associate Professor', 'أفلياكولوف نادر نظاموفيتش', 'مرشح في العلوم التقنية، أستاذ مشارك', 'Авлиякулов Надир Низомович', 'Кандидат технических наук, доцент'),
            $this->staffMember('metrology-standardization-quality-control-hasanova-zebo-davlatovna', 'Hasanova Zebo Davlatovna', 'PhD, Associate Professor', 'حسنوفا زيبو دولتوفنا', 'دكتوراه، أستاذة مشاركة', 'Хасанова Зебо Давлатовна', 'PhD, доцент'),
            $this->staffMember('metrology-standardization-quality-control-tosheva-gulnora-djurayevna', 'Tosheva Gulnora Djurayevna', 'PhD, Associate Professor', 'توشيفا غولنورا جوراييفنا', 'دكتوراه، أستاذة مشاركة', 'Тошева Гулнора Джураевна', 'PhD, доцент'),
            $this->staffMember('metrology-standardization-quality-control-davlyatova-mavlyuda-bakhtiyorovna', 'Davlyatova Mavlyuda Bakhtiyorovna', 'PhD, Associate Professor', 'دافلياتوفا مفلودة بختياروفنا', 'دكتوراه، أستاذة مشاركة', 'Давлятова Мавлюда Бахтиёровна', 'PhD, доцент'),
            $this->staffMember('metrology-standardization-quality-control-boltaeva-zulfiya-zarifovna', 'Boltaeva Zulfiya Zarifovna', 'Senior Lecturer', 'بولتايفا زلفية زاريفوفنا', 'محاضرة أولى', 'Болтаева Зулфия Зарифовна', 'Старший преподаватель'),
            $this->staffMember('metrology-standardization-quality-control-sayidakhmedov-ravshan-rajabovich', 'Sayidakhmedov Ravshan Rajabovich', 'Senior Lecturer', 'سيد أحمدوف رفشان رجبوفيتش', 'محاضر أول', 'Сайидахмедов Равшан Ражабович', 'Старший преподаватель'),
            $this->staffMember('metrology-standardization-quality-control-khaidarov-shukhrat-khikmatullayevich', 'Khaidarov Shukhrat Khikmatullayevich', 'Assistant', 'خايداروف شوخرات حكمتولايفيتش', 'مساعد', 'Хайдаров Шухрат Хикматуллаевич', 'Ассистент'),
            $this->staffMember('metrology-standardization-quality-control-shadiyev-suxrob-sadilloyevich', 'Shadiyev Suxrob Sadilloyevich', 'Assistant', 'شاديف سخراب سعد الله ييفيتش', 'مساعد', 'Шадиев Сухроб Садиллоевич', 'Ассистент'),
            $this->staffMember('metrology-standardization-quality-control-qarshiyev-zohid-abdurahim-ogli', "Qarshiyev Zohid Abdurahim o'g'li", 'Assistant', 'قارشييف زاهد عبد الرحيم أوغلي', 'مساعد', 'Каршиев Зохид Абдурахим угли', 'Ассистент'),
            $this->staffMember('metrology-standardization-quality-control-azimova-firuza-kamolovna', 'Azimova Firuza Kamolovna', 'Trainee Teacher', 'عظيموفا فيروزا كامولوفنا', 'معلمة متدربة', 'Азимова Фируза Камоловна', 'Преподаватель-стажер'),
            $this->staffMember('metrology-standardization-quality-control-yodgorova-mamura-orifovna', "Yodgorova Ma'mura Orifovna", 'Trainee Teacher', 'يودغوروفا معمورة عارفوفنا', 'معلمة متدربة', 'Ёдгорова Мамура Орифовна', 'Преподаватель-стажер'),
            $this->staffMember('metrology-standardization-quality-control-kamalova-mukhlisa-khudoyberdievna', 'Kamalova Mukhlisa Khudoyberdievna', 'Trainee Teacher', 'كامالوفا مخلصة خدويبردييفنا', 'معلمة متدربة', 'Камалова Мухлиса Худойбердиевна', 'Преподаватель-стажер'),
        ];
    }

    private function staffMember(string $slug, string $name, string $position, string $arName, string $arPosition, string $ruName, string $ruPosition): array
    {
        return [
            'slug' => $slug,
            'translations' => [
                'en' => ['name' => $name, 'position' => $position],
                'uz' => ['name' => $name, 'position' => $position],
                'ru' => ['name' => $ruName, 'position' => $ruPosition],
                'ar' => ['name' => $arName, 'position' => $arPosition],
            ],
        ];
    }
};

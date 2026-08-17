<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('departments') || ! Schema::hasTable('staff_profiles')) {
            return;
        }

        $departmentId = DB::table('departments')->where('slug', 'economics-and-management')->value('id');
        if (! $departmentId) {
            return;
        }

        DB::transaction(function () use ($departmentId) {
            $this->normalizeDepartment((int) $departmentId);
            $this->normalizeStaff((int) $departmentId);
            $this->normalizeSections((int) $departmentId);
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function normalizeDepartment(int $departmentId): void
    {
        DB::table('departments')->where('id', $departmentId)->update([
            'head_name' => 'Boboyev Akmal Choriyevich',
            'phone' => '+998 97 306 31 32',
            'email' => 'boboyevakmal1974@gmail.com',
            'reception_time' => 'Monday-Friday 14:00-16:00',
            'updated_at' => now(),
        ]);
    }

    private function normalizeStaff(int $departmentId): void
    {
        $canonicalSlugs = collect($this->staffProfiles())->pluck('slug')->all();
        $deleteSlugs = [
            'economics-and-management-basic-doctoral-student',
            'economics-and-management-head-of-the-cabinet-mukhitdinova',
            'economics-and-management-nigina-isomiddinovna',
            'economics-and-management-0-boboyev-akmal-chorievich',
            'economics-and-management-3-xalilova-muxabbat-nutfullayevna',
            'economics-and-management-5-xasanova-gulrux-djumanazarovna',
            'economics-and-management-6-azimov-bobir-fattoxovich',
            'economics-and-management-7-avezova-shaxnoza-maxmudjanovna',
            'economics-and-management-8-xalliyeva-nargiza-roziqovna',
            'economics-and-management-13-toshev-fazliddin-zaynitdinovich',
            'economics-and-management-16-raxmatov-shuxrat-axatovich',
            'economics-and-management-18-muxsinov-bekzod-toxirovich',
            'economics-and-management-19-akramova-obida-qosimovna',
            'economics-and-management-20-raxmonov-xurshid-xayriddinovich',
            'economics-and-management-21-muxitdinova-nigina-isomiddinovna',
            'economics-and-management-of-economic-sciences-azimov-bobir-fattokhovich',
        ];

        $ids = DB::table('staff_profiles')->whereIn('slug', $deleteSlugs)->pluck('id')->all();
        if ($ids !== []) {
            DB::table('staff_profile_translations')->whereIn('staff_profile_id', $ids)->delete();
            DB::table('staff_profiles')->whereIn('id', $ids)->delete();
        }

        DB::table('staff_profiles')->where('department_id', $departmentId)->whereNotIn('slug', $canonicalSlugs)->update([
            'is_active' => false,
            'updated_at' => now(),
        ]);

        foreach ($this->staffProfiles() as $index => $profile) {
            $profileId = DB::table('staff_profiles')->where('slug', $profile['slug'])->value('id');
            if (! $profileId) {
                $profileId = DB::table('staff_profiles')->insertGetId([
                    'slug' => $profile['slug'],
                    'department_id' => $departmentId,
                    'faculty_id' => DB::table('departments')->where('id', $departmentId)->value('faculty_id'),
                    'photo' => null,
                    'email' => null,
                    'phone' => null,
                    'sort_order' => $index + 10,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('staff_profiles')->where('id', $profileId)->update([
                'department_id' => $departmentId,
                'sort_order' => $index + 10,
                'is_active' => true,
                'updated_at' => now(),
            ]);

            foreach ($this->locales() as $locale) {
                $name = $profile['names'][$locale] ?? $profile['names']['en'];
                $position = $this->position($profile['position'], $locale);
                DB::table('staff_profile_translations')->updateOrInsert(
                    ['staff_profile_id' => $profileId, 'locale' => $locale],
                    [
                        'full_name' => $name,
                        'position' => $position,
                        'bio' => $this->bio($name, $position, $locale),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    private function normalizeSections(int $departmentId): void
    {
        if (! Schema::hasTable('department_translations')) {
            return;
        }

        foreach ($this->locales() as $locale) {
            $translation = DB::table('department_translations')->where('department_id', $departmentId)->where('locale', $locale)->first();
            if (! $translation) {
                continue;
            }

            $sections = json_decode((string) $translation->content_sections, true);
            if (! is_array($sections)) {
                $sections = [];
            }

            $sections = $this->replaceSection($sections, [
                'key' => 'staff',
                'title' => $this->label($locale),
                'items' => $this->staffSectionItems($locale),
            ]);

            DB::table('department_translations')->where('id', $translation->id)->update([
                'content_sections' => json_encode(array_values($sections), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ]);
        }
    }

    private function staffProfiles(): array
    {
        return [
            ['slug' => 'economics-and-management-boboyev-akmal-choriyevich', 'names' => ['en' => 'Boboyev Akmal Choriyevich', 'uz' => 'Boboyev Akmal Choriyevich', 'ru' => 'Бобоев Акмал Чориевич', 'ar' => 'بوبوييف أكمل تشورييفيتش'], 'position' => 'Head of Department, Associate Professor, PhD'],
            ['slug' => 'economics-and-management-nizamov-asliddin-badritdinovich', 'names' => ['en' => 'Nizamov Asliddin Badritdinovich', 'uz' => 'Nizamov Asliddin Badritdinovich', 'ru' => 'Низамов Аслиддин Бадритдинович', 'ar' => 'نيزاموف أصل الدين بدريتدينوفيتش'], 'position' => 'Candidate of Economic Sciences, Professor'],
            ['slug' => 'economics-and-management-ochilov-sherali-barotovich', 'names' => ['en' => 'Ochilov Sherali Barotovich', 'uz' => 'Ochilov Sherali Barotovich', 'ru' => 'Очилов Шерали Баротович', 'ar' => 'أوتشيلوف شيرعلي باراتوفيتش'], 'position' => 'Candidate of Economic Sciences, Associate Professor'],
            ['slug' => 'economics-and-management-khalyova-mukhabbat-nutfullaevna', 'names' => ['en' => 'Khalyova Mukhabbat Nutfullaevna', 'uz' => 'Xalilova Muxabbat Nutfullayevna', 'ru' => 'Халилова Мухаббат Нутфуллаевна', 'ar' => 'خليلوفا محبت نوتفوللاييفنا'], 'position' => 'Associate Professor'],
            ['slug' => 'economics-and-management-hamidov-oqil-raxmonovich', 'names' => ['en' => 'Hamidov Oqil Raxmonovich', 'uz' => 'Hamidov Oqil Raxmonovich', 'ru' => 'Хамидов Окил Рахмонович', 'ar' => 'حميدوف عاقل رحمانوفيتش'], 'position' => 'Associate Professor'],
            ['slug' => 'economics-and-management-hasanova-gulrukh-dzhumanazarovna', 'names' => ['en' => 'Hasanova Gulrukh Dzhumanazarovna', 'uz' => 'Xasanova Gulrux Djumanazarovna', 'ru' => 'Хасанова Гулрух Джуманазаровна', 'ar' => 'حسنوفا غولروخ جومانازاروفنا'], 'position' => 'Candidate of Economic Sciences, Associate Professor'],
            ['slug' => 'economics-and-management-azimov-bobir-fattoxovich', 'names' => ['en' => 'Azimov Bobir Fattoxovich', 'uz' => 'Azimov Bobir Fattoxovich', 'ru' => 'Азимов Бобир Фаттохович', 'ar' => 'عظيموف بوبير فتّاحوفيتش'], 'position' => 'Candidate of Economic Sciences, Associate Professor'],
            ['slug' => 'economics-and-management-avezova-shakhnoza-makhmudjanovna', 'names' => ['en' => 'Avezova Shakhnoza Makhmudjanovna', 'uz' => 'Avezova Shaxnoza Maxmudjanovna', 'ru' => 'Авезова Шахноза Махмуджановна', 'ar' => 'أفيزوفا شاهنوزا محمودجانوفنا'], 'position' => 'PhD, Associate Professor'],
            ['slug' => 'economics-and-management-khaliyeva-nargiza-roziqovna', 'names' => ['en' => 'Khaliyeva Nargiza Roziqovna', 'uz' => 'Xalliyeva Nargiza Roziqovna', 'ru' => 'Халиева Наргиза Розиковна', 'ar' => 'خلييفا نرجيزا روزيقوفنا'], 'position' => 'PhD, Associate Professor'],
            ['slug' => 'economics-and-management-yuldasheva-saida-nematovna', 'names' => ['en' => "Yuldasheva Saida Ne'matovna", 'uz' => 'Yuldasheva Saida Ne’matovna', 'ru' => 'Юлдашева Саида Неъматовна', 'ar' => 'يولداشيفا سعيدة نعمتوفنا'], 'position' => 'Associate Professor'],
            ['slug' => 'economics-and-management-jumayeva-dilafroz-hamroyevna', 'names' => ['en' => "Jumayeva Dilafro‘z Hamroyevna", 'uz' => 'Jumayeva Dilafro‘z Hamroyevna', 'ru' => 'Жумаева Дилафруз Хамроевна', 'ar' => 'جمعييفا ديلفروز حمرويفنا'], 'position' => 'PhD, Associate Professor'],
            ['slug' => 'economics-and-management-rasulova-nigora-nematovna', 'names' => ['en' => 'Rasulova Nigora Nematovna', 'uz' => 'Rasulova Nigora Nematovna', 'ru' => 'Расулова Нигора Нематовна', 'ar' => 'رسولوفا نيغورا نعمتوفنا'], 'position' => 'Senior Lecturer'],
            ['slug' => 'economics-and-management-usmanova-nasiba-yunusovna', 'names' => ['en' => 'Usmanova Nasiba Yunusovna', 'uz' => 'Usmanova Nasiba Yunusovna', 'ru' => 'Усманова Насиба Юнусовна', 'ar' => 'عثمانوفا نسيبة يونسوفنا'], 'position' => 'Senior Lecturer'],
            ['slug' => 'economics-and-management-tashev-fazliddin-zaynitdinovich', 'names' => ['en' => 'Toshev Fazliddin Zaynitdinovich', 'uz' => 'Toshev Fazliddin Zaynitdinovich', 'ru' => 'Тошев Фазлиддин Зайнитдинович', 'ar' => 'توشيف فضل الدين زاينيتدينوفيتش'], 'position' => 'Senior Lecturer'],
            ['slug' => 'economics-and-management-narzulloyeva-feruza-fatulloyevna', 'names' => ['en' => 'Narzulloyeva Feruza Fatulloyevna', 'uz' => 'Narzulloyeva Feruza Fatulloyevna', 'ru' => 'Нарзуллоева Феруза Фатуллоевна', 'ar' => 'نرزوللاييفا فيروزا فتوللاييفنا'], 'position' => 'Senior Lecturer'],
            ['slug' => 'economics-and-management-sulaymonov-azamat-ilhomovich', 'names' => ['en' => 'Sulaymonov Azamat Ilhomovich', 'uz' => 'Sulaymonov Azamat Ilhomovich', 'ru' => 'Сулаймонов Азамат Илхомович', 'ar' => 'سليمانوف عزمت إلهوموفيتش'], 'position' => 'Assistant'],
            ['slug' => 'economics-and-management-rakhmatov-shukhrat-akhatovich', 'names' => ['en' => 'Rakhmatov Shukhrat Akhatovich', 'uz' => 'Raxmatov Shuxrat Axatovich', 'ru' => 'Рахматов Шухрат Ахатович', 'ar' => 'رحمتوف شوخرات أخاتوفيتش'], 'position' => 'Doctoral Student'],
            ['slug' => 'economics-and-management-jumaeva-zulfiya-qayumovna', 'names' => ['en' => 'Jumaeva Zulfiya Qayumovna', 'uz' => 'Jumayeva Zulfiya Qayumovna', 'ru' => 'Жумаева Зулфия Каюмовна', 'ar' => 'جمعييفا زلفية قيوموفنا'], 'position' => 'Doctoral Student'],
            ['slug' => 'economics-and-management-akramova-obida-kasimovna', 'names' => ['en' => 'Akramova Obida Qosimovna', 'uz' => 'Akramova Obida Qosimovna', 'ru' => 'Акрамова Обида Косимовна', 'ar' => 'أكراموفا عبيدة قاسيموفنا'], 'position' => 'Doctoral Student'],
            ['slug' => 'economics-and-management-muxsinov-bekzod-toxirovich', 'names' => ['en' => 'Muxsinov Bekzod Toxirovich', 'uz' => 'Muxsinov Bekzod Toxirovich', 'ru' => 'Мухсинов Бекзод Тохирович', 'ar' => 'مخسينوف بيكزود توخيروفيتش'], 'position' => 'Doctoral Student'],
            ['slug' => 'economics-and-management-raxmonov-xurshid-xayriddinovich', 'names' => ['en' => 'Raxmonov Xurshid Xayriddinovich', 'uz' => 'Raxmonov Xurshid Xayriddinovich', 'ru' => 'Рахмонов Хуршид Хайриддинович', 'ar' => 'رحمانوف خورشيد خير الدينوفيتش'], 'position' => 'Doctoral Student'],
            ['slug' => 'economics-and-management-muxitdinova-nigina-isomiddinovna', 'names' => ['en' => 'Muxitdinova Nigina Isomiddinovna', 'uz' => 'Muxitdinova Nigina Isomiddinovna', 'ru' => 'Мухитдинова Нигина Исомиддиновна', 'ar' => 'مخيتدينوفا نيغينا إيسوم الدينوفنا'], 'position' => 'Cabinet Manager'],
        ];
    }

    private function staffSectionItems(string $locale): array
    {
        return collect($this->staffProfiles())->map(function (array $profile) use ($locale) {
            $name = $profile['names'][$locale] ?? $profile['names']['en'];
            return $name."\n".$this->position($profile['position'], $locale);
        })->all();
    }

    private function position(string $position, string $locale): string
    {
        $map = [
            'Head of Department, Associate Professor, PhD' => ['en' => 'Head of Department, Associate Professor, PhD', 'uz' => 'Kafedra mudiri, dotsent, PhD', 'ru' => 'Заведующий кафедрой, доцент, PhD', 'ar' => 'رئيس القسم، أستاذ مشارك، دكتوراه'],
            'Candidate of Economic Sciences, Professor' => ['en' => 'Candidate of Economic Sciences, Professor', 'uz' => 'Iqtisod fanlari nomzodi, professor', 'ru' => 'Кандидат экономических наук, профессор', 'ar' => 'مرشح في العلوم الاقتصادية، أستاذ'],
            'Candidate of Economic Sciences, Associate Professor' => ['en' => 'Candidate of Economic Sciences, Associate Professor', 'uz' => 'Iqtisod fanlari nomzodi, dotsent', 'ru' => 'Кандидат экономических наук, доцент', 'ar' => 'مرشح في العلوم الاقتصادية، أستاذ مشارك'],
            'Associate Professor' => ['en' => 'Associate Professor', 'uz' => 'Dotsent', 'ru' => 'Доцент', 'ar' => 'أستاذ مشارك'],
            'PhD, Associate Professor' => ['en' => 'PhD, Associate Professor', 'uz' => 'PhD, dotsent', 'ru' => 'PhD, доцент', 'ar' => 'دكتوراه، أستاذ مشارك'],
            'Senior Lecturer' => ['en' => 'Senior Lecturer', 'uz' => 'Katta o‘qituvchi', 'ru' => 'Старший преподаватель', 'ar' => 'محاضر أول'],
            'Assistant' => ['en' => 'Assistant', 'uz' => 'Assistent', 'ru' => 'Ассистент', 'ar' => 'مساعد'],
            'Doctoral Student' => ['en' => 'Doctoral Student', 'uz' => 'Doktorant', 'ru' => 'Докторант', 'ar' => 'طالب دكتوراه'],
            'Cabinet Manager' => ['en' => 'Cabinet Manager', 'uz' => 'Kabinet mudiri', 'ru' => 'Заведующая кабинетом', 'ar' => 'مديرة المكتب'],
        ];
        return $map[$position][$locale] ?? $position;
    }

    private function bio(string $name, string $position, string $locale): string
    {
        return match ($locale) {
            'uz' => "{$name} Iqtisodiyot va menejment kafedrasida {$position} sifatida faoliyat yuritadi.",
            'ru' => "{$name} работает на кафедре экономики и менеджмента в должности «{$position}».",
            'ar' => "{$name} يعمل/تعمل في قسم الاقتصاد والإدارة بصفة {$position}.",
            default => "{$name} serves as {$position} in the Department of Economics and Management.",
        };
    }

    private function label(string $locale): string
    {
        return match ($locale) {
            'uz' => 'Kafedra professor-o‘qituvchilari',
            'ru' => 'Профессорско-преподавательский состав кафедры',
            'ar' => 'أعضاء هيئة التدريس في القسم',
            default => 'Professor-Teachers of the Department',
        };
    }

    private function replaceSection(array $sections, array $replacement): array
    {
        $found = false;
        foreach ($sections as $index => $section) {
            if (($section['key'] ?? null) === $replacement['key']) {
                $sections[$index] = $replacement;
                $found = true;
            }
        }
        if (! $found) {
            $sections[] = $replacement;
        }
        return $sections;
    }

    private function locales(): array
    {
        return ['en', 'uz', 'ru', 'ar'];
    }
};

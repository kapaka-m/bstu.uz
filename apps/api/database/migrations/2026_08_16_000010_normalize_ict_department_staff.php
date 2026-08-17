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

        $departmentId = DB::table('departments')->where('slug', 'information-and-communication-technologies')->value('id');
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
            'head_name' => 'Atoyev Fazliddin Sayfiddinovich',
            'phone' => '+998 99 380 81 88',
            'email' => null,
            'reception_time' => 'Monday-Friday 14:00-16:00',
            'updated_at' => now(),
        ]);
    }

    private function normalizeStaff(int $departmentId): void
    {
        $canonicalSlugs = collect($this->staffProfiles())->pluck('slug')->all();
        $deleteSlugs = [
            'information-and-communication-technologies-of-physical-and-mathematical-sciences-yuldoshev-shukhrat-savrievich',
            'information-and-communication-technologies-teacher-trainee-kadirova',
            'information-and-communication-technologies-shahibonu-mukhammadovna',
            'information-and-communication-technologies-akhtamova-laziza-azamovna',
            'information-and-communication-technologies-teacher-trainee-atoev',
            'information-and-communication-technologies-teacher-trainee',
            'information-and-communication-technologies-teacher-trainee-toirov',
            'information-and-communication-technologies-mirshod-mirkhonovich',
            'information-and-communication-technologies-1-yoldoshev-shuhrat-savrievich',
            'information-and-communication-technologies-6-sariyev-rustam-bobomuradovich',
            'information-and-communication-technologies-8-nafasov-mirzomurod-muxamadovich',
            'information-and-communication-technologies-12-nurullaev-mirxon-muhammadovich',
            'information-and-communication-technologies-16-saidov-usmon-bahron-ogli',
            'information-and-communication-technologies-18-kadirova-shoxibonu-muxammadovna',
            'information-and-communication-technologies-19-axtamova-laziza-azam-qizi',
            'information-and-communication-technologies-21-murtazoev-azamat-sunatillo-ogli',
            'information-and-communication-technologies-22-toirov-mirshod-mirxonovich',
            'information-and-communication-technologies-23-kamolova-mahliyo-hasanovna',
        ];

        $ids = DB::table('staff_profiles')->whereIn('slug', $deleteSlugs)->pluck('id')->all();
        if ($ids !== []) {
            DB::table('staff_profile_translations')->whereIn('staff_profile_id', $ids)->delete();
            DB::table('staff_profiles')->whereIn('id', $ids)->delete();
        }

        DB::table('staff_profiles')
            ->where('department_id', $departmentId)
            ->whereNotIn('slug', $canonicalSlugs)
            ->update(['is_active' => false, 'updated_at' => now()]);

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
                'title' => $this->label($locale, 'staff'),
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
            ['slug' => 'information-and-communication-technologies-atoyev-fazliddin-sayfiddinovich', 'names' => ['en' => 'Atoyev Fazliddin Sayfiddinovich', 'uz' => 'Atoyev Fazliddin Sayfiddinovich', 'ru' => 'Атоев Фазлиддин Сайфиддинович', 'ar' => 'أتوييف فضل الدين سيف الدينوفيتش'], 'position' => 'Head of Department'],
            ['slug' => 'information-and-communication-technologies-narziev-umidzhon-zaripovich', 'names' => ['en' => 'Narziev Umidzhon Zaripovich', 'uz' => 'Narziyev Umidjon Zaripovich', 'ru' => 'Нарзиев Умиджон Зарипович', 'ar' => 'نارزييف أوميدجون زاريبوفيتش'], 'position' => 'Senior Lecturer'],
            ['slug' => 'information-and-communication-technologies-yuldoshev-shukhrat-savrievich', 'names' => ['en' => 'Yuldoshev Shukhrat Savrievich', 'uz' => 'Yo‘ldoshev Shuhrat Savriyevich', 'ru' => 'Юлдошев Шухрат Савриевич', 'ar' => 'يولدوشيف شوخرات سافرييفيتش'], 'position' => 'Candidate of Physical and Mathematical Sciences, Associate Professor'],
            ['slug' => 'information-and-communication-technologies-murodova-zarina-rashidovna', 'names' => ['en' => 'Murodova Zarina Rashidovna', 'uz' => 'Murodova Zarina Rashidovna', 'ru' => 'Муродова Зарина Рашидовна', 'ar' => 'مورودوفا زارينا رشيدوفنا'], 'position' => 'PhD in Pedagogical Sciences, Associate Professor'],
            ['slug' => 'information-and-communication-technologies-muradova-firuza-rashidovna', 'names' => ['en' => 'Muradova Firuza Rashidovna', 'uz' => 'Muradova Firuza Rashidovna', 'ru' => 'Мурадова Фируза Рашидовна', 'ar' => 'مرادوفا فيروزا رشيدوفنا'], 'position' => 'PhD in Pedagogy, Associate Professor'],
            ['slug' => 'information-and-communication-technologies-yuldashev-askar-adizovich', 'names' => ['en' => 'Yuldashev Askar Adizovich', 'uz' => 'Yo‘ldoshev Asqar Adizovich', 'ru' => 'Юлдашев Аскар Адизович', 'ar' => 'يولداشيف أسكر أديزوفيتش'], 'position' => 'PhD, Associate Professor'],
            ['slug' => 'information-and-communication-technologies-asraev-zafar-rizakulovich', 'names' => ['en' => 'Asraev Zafar Rizakulovich', 'uz' => 'Asrayev Zafar Rizakulovich', 'ru' => 'Асраев Зафар Ризакулович', 'ar' => 'أسراييف ظفر ريزاقولوفيتش'], 'position' => 'Senior Lecturer'],
            ['slug' => 'information-and-communication-technologies-sariev-rustam-bobomuradovich', 'names' => ['en' => 'Sariyev Rustam Bobomuradovich', 'uz' => 'Sariyev Rustam Bobomuradovich', 'ru' => 'Сариев Рустам Бобомуродович', 'ar' => 'سارييف رستام بوبومورادوفيتش'], 'position' => 'Senior Lecturer'],
            ['slug' => 'information-and-communication-technologies-sohibov-tolib-fayzullaevich', 'names' => ['en' => 'Sohibov Tolib Fayzullaevich', 'uz' => 'Sohibov Tolib Fayzullayevich', 'ru' => 'Сохибов Толиб Файзуллаевич', 'ar' => 'صاحبوف طالب فيض الله ييفيتش'], 'position' => 'Senior Lecturer'],
            ['slug' => 'information-and-communication-technologies-nafasov-mirzomurod-mukhamadovich', 'names' => ['en' => 'Nafasov Mirzomurod Mukhamadovich', 'uz' => 'Nafasov Mirzomurod Muxamadovich', 'ru' => 'Нафасов Мирзомурод Мухамадович', 'ar' => 'نفاسوف ميرزومورود محمدوفيتش'], 'position' => 'Senior Lecturer'],
            ['slug' => 'information-and-communication-technologies-muxamadieva-zarina-bahodirovna', 'names' => ['en' => 'Muxamadieva Zarina Bahodirovna', 'uz' => 'Muxamadiyeva Zarina Bahodirovna', 'ru' => 'Мухамадиева Зарина Баходировна', 'ar' => 'محمدييفا زارينا باهوديروفنا'], 'position' => 'Assistant'],
            ['slug' => 'information-and-communication-technologies-joraev-olim-ismoilovich', 'names' => ['en' => 'Jo‘raev Olim Ismoilovich', 'uz' => 'Jo‘rayev Olim Ismoilovich', 'ru' => 'Жураев Олим Исмоилович', 'ar' => 'جوراييف أوليم إسماعيلوفيتش'], 'position' => 'Assistant'],
            ['slug' => 'information-and-communication-technologies-hayitova-iroda-ilhomovna', 'names' => ['en' => 'Hayitova Iroda Ilhomovna', 'uz' => 'Hayitova Iroda Ilhomovna', 'ru' => 'Хайитова Ирода Илхомовна', 'ar' => 'حايتوفا إرادة إلهوموفنا'], 'position' => 'Senior Lecturer'],
            ['slug' => 'information-and-communication-technologies-nurullaev-mirkhon-muhammadovich', 'names' => ['en' => 'Nurullaev Mirkhon Muhammadovich', 'uz' => 'Nurullayev Mirxon Muhammadovich', 'ru' => 'Нуруллаев Мирхон Мухаммадович', 'ar' => 'نوروللاييف ميرخون محمدوفيتش'], 'position' => 'Senior Lecturer'],
            ['slug' => 'information-and-communication-technologies-turaeva-gulchiroy-sheralievna', 'names' => ['en' => 'Turaeva Gulchiroy Sheralievna', 'uz' => 'Turayeva Gulchiroy Sheraliyevna', 'ru' => 'Тураева Гулчирой Шералиевна', 'ar' => 'توراييفا غولتشيروي شيرالييفنا'], 'position' => 'Assistant'],
            ['slug' => 'information-and-communication-technologies-sharapova-nigora-amonovna', 'names' => ['en' => 'Sharapova Nigora Amonovna', 'uz' => 'Sharapova Nigora Amonovna', 'ru' => 'Шарапова Нигора Амоновна', 'ar' => 'شارابوفا نيغورا أمونوفنا'], 'position' => 'Assistant'],
            ['slug' => 'information-and-communication-technologies-gaffarov-laziz-xasanovich', 'names' => ['en' => 'Gaffarov Laziz Xasanovich', 'uz' => 'Gaffarov Laziz Xasanovich', 'ru' => 'Гаффаров Лазиз Хасанович', 'ar' => 'غفاروف لازيز حسنوفيتش'], 'position' => 'PhD, Associate Professor'],
            ['slug' => 'information-and-communication-technologies-saidov-usman-bahran-oglu', 'names' => ['en' => 'Saidov Usmon Bahron o‘g‘li', 'uz' => 'Saidov Usmon Bahron o‘g‘li', 'ru' => 'Саидов Усмон Бахрон угли', 'ar' => 'سعيدوف عثمان بحرون أوغلي'], 'position' => 'Trainee Teacher'],
            ['slug' => 'information-and-communication-technologies-talabov-mirshod-dishodovich', 'names' => ['en' => 'Talabov Mirshod Dilshodovich', 'uz' => 'Talabov Mirshod Dilshodovich', 'ru' => 'Талабов Миршод Дилшодович', 'ar' => 'طالبوف ميرشود ديلشودوفيتش'], 'position' => 'Senior Lecturer'],
            ['slug' => 'information-and-communication-technologies-kadirova-shoxibonu-muxammadovna', 'names' => ['en' => 'Kadirova Shoxibonu Muxammadovna', 'uz' => 'Qodirova Shohibonu Muxammadovna', 'ru' => 'Кадирова Шохибону Мухаммадовна', 'ar' => 'قاديروفا شوهيبونو محمدوفنا'], 'position' => 'Trainee Teacher'],
            ['slug' => 'information-and-communication-technologies-axtamova-laziza-azam-qizi', 'names' => ['en' => 'Axtamova Laziza Azam qizi', 'uz' => 'Axtamova Laziza Azam qizi', 'ru' => 'Ахтамова Лазиза Азам кизи', 'ar' => 'أختاموفا لازيزا أعظم قيزي'], 'position' => 'Trainee Teacher'],
            ['slug' => 'information-and-communication-technologies-murtazoev-azamat-sunatillo-ogli', 'names' => ['en' => 'Murtazoev Azamat Sunatillo o‘g‘li', 'uz' => 'Murtazoyev Azamat Sunatillo o‘g‘li', 'ru' => 'Муртазоев Азамат Сунатилло угли', 'ar' => 'مرتضاييف عزمت سناتيلو أوغلي'], 'position' => 'Trainee Teacher'],
            ['slug' => 'information-and-communication-technologies-toirov-mirshod-mirxonovich', 'names' => ['en' => 'Toirov Mirshod Mirxonovich', 'uz' => 'Toirov Mirshod Mirxonovich', 'ru' => 'Тоиров Миршод Мирхонович', 'ar' => 'طائروف ميرشود ميرخونوفيتش'], 'position' => 'Trainee Teacher'],
            ['slug' => 'information-and-communication-technologies-kamolova-mahliyo-hasanovna', 'names' => ['en' => 'Kamolova Mahliyo Hasanovna', 'uz' => 'Kamolova Mahliyo Hasanovna', 'ru' => 'Камолова Махлиё Хасановна', 'ar' => 'كامولوفا ماهليو حسنوفنا'], 'position' => 'Trainee Teacher'],
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
            'Head of Department' => ['en' => 'Head of Department', 'uz' => 'Kafedra mudiri', 'ru' => 'Заведующий кафедрой', 'ar' => 'رئيس القسم'],
            'Senior Lecturer' => ['en' => 'Senior Lecturer', 'uz' => 'Katta o‘qituvchi', 'ru' => 'Старший преподаватель', 'ar' => 'محاضر أول'],
            'Candidate of Physical and Mathematical Sciences, Associate Professor' => ['en' => 'Candidate of Physical and Mathematical Sciences, Associate Professor', 'uz' => 'Fizika-matematika fanlari nomzodi, dotsent', 'ru' => 'Кандидат физико-математических наук, доцент', 'ar' => 'مرشح في العلوم الفيزيائية والرياضية، أستاذ مشارك'],
            'PhD in Pedagogical Sciences, Associate Professor' => ['en' => 'PhD in Pedagogical Sciences, Associate Professor', 'uz' => 'Pedagogika fanlari bo‘yicha PhD, dotsent', 'ru' => 'PhD по педагогическим наукам, доцент', 'ar' => 'دكتوراه في العلوم التربوية، أستاذ مشارك'],
            'PhD in Pedagogy, Associate Professor' => ['en' => 'PhD in Pedagogy, Associate Professor', 'uz' => 'Pedagogika bo‘yicha PhD, dotsent', 'ru' => 'PhD по педагогике, доцент', 'ar' => 'دكتوراه في التربية، أستاذ مشارك'],
            'PhD, Associate Professor' => ['en' => 'PhD, Associate Professor', 'uz' => 'PhD, dotsent', 'ru' => 'PhD, доцент', 'ar' => 'دكتوراه، أستاذ مشارك'],
            'Assistant' => ['en' => 'Assistant', 'uz' => 'Assistent', 'ru' => 'Ассистент', 'ar' => 'مساعد'],
            'Trainee Teacher' => ['en' => 'Trainee Teacher', 'uz' => 'O‘qituvchi-stajor', 'ru' => 'Преподаватель-стажер', 'ar' => 'مدرس متدرب'],
        ];

        return $map[$position][$locale] ?? $position;
    }

    private function bio(string $name, string $position, string $locale): string
    {
        return match ($locale) {
            'uz' => "{$name} Axborot-kommunikatsiya texnologiyalari kafedrasida {$position} sifatida faoliyat yuritadi.",
            'ru' => "{$name} работает на кафедре информационно-коммуникационных технологий в должности «{$position}».",
            'ar' => "{$name} يعمل/تعمل في قسم تقنيات المعلومات والاتصالات بصفة {$position}.",
            default => "{$name} serves as {$position} in the Department of Information and Communication Technologies.",
        };
    }

    private function label(string $locale, string $key): string
    {
        $labels = [
            'staff' => ['en' => 'Professor-Teachers of the Department', 'uz' => 'Kafedra professor-o‘qituvchilari', 'ru' => 'Профессорско-преподавательский состав кафедры', 'ar' => 'أعضاء هيئة التدريس في القسم'],
        ];

        return $labels[$key][$locale] ?? $labels[$key]['en'];
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

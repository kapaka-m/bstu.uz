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

        $departmentId = DB::table('departments')->where('slug', 'uzbek-foreign-languages')->value('id');
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
            'head_name' => 'Yusupova Shokhida Batirovna',
            'phone' => '+998 93 623 74 72',
            'email' => null,
            'reception_time' => 'Daily 14:00-16:00',
            'updated_at' => now(),
        ]);
    }

    private function normalizeStaff(int $departmentId): void
    {
        $deleteSlugs = [
            'uzbek-foreign-languages-yusupova-shoxida-batirovna',
            'uzbek-foreign-languages-0-dr-dilfuza-a-saidova',
            'uzbek-foreign-languages-1-mavjuda-b-sharipova',
            'uzbek-foreign-languages-2-aziza-s-jamilova',
        ];

        $ids = DB::table('staff_profiles')->whereIn('slug', $deleteSlugs)->pluck('id')->all();
        if ($ids !== []) {
            DB::table('staff_profile_translations')->whereIn('staff_profile_id', $ids)->delete();
            DB::table('staff_profiles')->whereIn('id', $ids)->delete();
        }

        $oldAlimovaSlug = 'uzbek-foreign-languages-phd-dotsent-vb';
        $newAlimovaSlug = 'uzbek-foreign-languages-alimova-nozima-rajabboyevna';
        if (! DB::table('staff_profiles')->where('slug', $newAlimovaSlug)->exists()) {
            DB::table('staff_profiles')->where('slug', $oldAlimovaSlug)->update([
                'slug' => $newAlimovaSlug,
                'updated_at' => now(),
            ]);
        }

        $canonicalSlugs = collect($this->staffProfiles())->pluck('slug')->all();
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
                    'phone' => $index === 0 ? '+998 93 623 74 72' : null,
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
            $translation = DB::table('department_translations')
                ->where('department_id', $departmentId)
                ->where('locale', $locale)
                ->first();

            if (! $translation) {
                continue;
            }

            $sections = json_decode((string) $translation->content_sections, true);
            if (! is_array($sections)) {
                $sections = [];
            }

            $sections = $this->replaceSection($sections, ['key' => 'overview', 'title' => $this->label($locale, 'overview'), 'items' => [$this->overview($locale)]]);
            $sections = $this->replaceSection($sections, ['key' => 'prepared_specialists', 'title' => $this->label($locale, 'programs'), 'items' => [$this->programsText($locale)]]);
            $sections = $this->replaceSection($sections, ['key' => 'subjects', 'title' => $this->label($locale, 'subjects'), 'items' => [$this->subjectsText($locale)]]);
            $sections = $this->replaceSection($sections, ['key' => 'staff', 'title' => $this->label($locale, 'staff'), 'items' => $this->staffSectionItems($locale)]);
            $sections = $this->replaceSection($sections, ['key' => 'research', 'title' => $this->label($locale, 'research'), 'items' => $this->researchItems($locale)]);
            $sections = $this->replaceSection($sections, ['key' => 'international_cooperation', 'title' => $this->label($locale, 'cooperation'), 'items' => $this->cooperationItems($locale)]);

            $sections = array_values(array_filter($sections, function (array $section): bool {
                $key = $section['key'] ?? '';
                return ! str_contains($key, 'structure') && $key !== 'department_structure';
            }));

            DB::table('department_translations')->where('id', $translation->id)->update([
                'content_sections' => json_encode($sections, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ]);
        }
    }

    private function overview(string $locale): string
    {
        return match ($locale) {
            'uz' => 'Kafedra talabalarning ona tili savodxonligi, xorijiy tillarda erkin muloqoti, tarjima, akademik yozuv va kasbiy kommunikatsiya ko‘nikmalarini rivojlantiradi.',
            'ru' => 'Кафедра развивает у студентов владение родным языком, свободное общение на иностранных языках, перевод, академическое письмо и профессиональную коммуникацию.',
            'ar' => 'يطور القسم كفاءة الطلاب في اللغة الأم والطلاقة في اللغات الأجنبية والترجمة والكتابة الأكاديمية ومهارات التواصل المهني.',
            default => 'The department develops students native-language competence, foreign-language fluency, translation, academic writing, and professional communication skills.',
        };
    }

    private function programsText(string $locale): string
    {
        return match ($locale) {
            'uz' => 'Kafedra universitetning barcha yo‘nalishlarida til, tarjima, akademik yozuv va kasbiy muloqot fanlarini o‘qitadi.',
            'ru' => 'Кафедра преподает языковые дисциплины, перевод, академическое письмо и профессиональную коммуникацию для всех направлений университета.',
            'ar' => 'يدرّس القسم مقررات اللغات والترجمة والكتابة الأكاديمية والتواصل المهني لجميع اتجاهات الجامعة.',
            default => 'The department teaches language, translation, academic writing, and professional communication courses across the university programs.',
        };
    }

    private function subjectsText(string $locale): string
    {
        return 'Bachelor subjects:'."\n".collect($this->subjects($locale))->map(fn (string $item, int $index) => ($index + 1).'. '.$item)->implode("\n");
    }

    private function subjects(string $locale): array
    {
        return match ($locale) {
            'uz' => ['O‘zbek tili', 'Rus tili', 'Ingliz tili', 'Nemis tili', 'Fransuz tili', 'Akademik yozuv', 'Tarjima nazariyasi va amaliyoti', 'Kasbiy kommunikatsiya'],
            'ru' => ['Узбекский язык', 'Русский язык', 'Английский язык', 'Немецкий язык', 'Французский язык', 'Академическое письмо', 'Теория и практика перевода', 'Профессиональная коммуникация'],
            'ar' => ['اللغة الأوزبكية', 'اللغة الروسية', 'اللغة الإنجليزية', 'اللغة الألمانية', 'اللغة الفرنسية', 'الكتابة الأكاديمية', 'نظرية الترجمة وتطبيقاتها', 'التواصل المهني'],
            default => ['Uzbek Language', 'Russian Language', 'English Language', 'German Language', 'French Language', 'Academic Writing', 'Translation Theory and Practice', 'Professional Communication'],
        };
    }

    private function researchItems(string $locale): array
    {
        return match ($locale) {
            'uz' => ['Xorijiy tillarni o‘qitish metodikasi va kommunikativ kompetensiyani rivojlantirish.', 'Tarjimashunoslik, qiyosiy adabiyotshunoslik va lingvistik tahlil bo‘yicha tadqiqotlar.', 'O‘zbek va xorijiy tillarda akademik yozuv ko‘nikmalarini takomillashtirish.', 'Kasbiy muloqot va terminologiyani o‘qitishning zamonaviy usullarini joriy etish.'],
            'ru' => ['Методика преподавания иностранных языков и развитие коммуникативной компетенции.', 'Исследования в области переводоведения, сравнительного литературоведения и лингвистического анализа.', 'Совершенствование навыков академического письма на узбекском и иностранных языках.', 'Внедрение современных методов обучения профессиональной коммуникации и терминологии.'],
            'ar' => ['منهجية تدريس اللغات الأجنبية وتطوير الكفاءة التواصلية.', 'بحوث في دراسات الترجمة والأدب المقارن والتحليل اللغوي.', 'تطوير مهارات الكتابة الأكاديمية باللغات الأوزبكية والأجنبية.', 'تطبيق الأساليب الحديثة في تدريس التواصل المهني والمصطلحات المتخصصة.'],
            default => ['Methodology of foreign-language teaching and development of communicative competence.', 'Research in translation studies, comparative literature, and linguistic analysis.', 'Improvement of academic writing skills in Uzbek and foreign languages.', 'Implementation of modern methods for teaching professional communication and terminology.'],
        };
    }

    private function cooperationItems(string $locale): array
    {
        return match ($locale) {
            'uz' => ['A.S. Pushkin nomidagi Rus tili davlat instituti bilan akademik hamkorlik.', 'Til o‘qitish metodikasi va akademik almashinuv bo‘yicha hamkorlik.', 'Xorijiy tillarni o‘qitishda zamonaviy resurslar va qo‘shma seminarlar.'],
            'ru' => ['Академическое сотрудничество с Государственным институтом русского языка имени А.С. Пушкина.', 'Сотрудничество по методике преподавания языков и академическому обмену.', 'Современные ресурсы и совместные семинары по преподаванию иностранных языков.'],
            'ar' => ['تعاون أكاديمي مع معهد بوشكين الحكومي للغة الروسية.', 'تعاون في منهجية تدريس اللغات والتبادل الأكاديمي.', 'موارد حديثة وندوات مشتركة في تدريس اللغات الأجنبية.'],
            default => ['Academic cooperation with Pushkin State Russian Language Institute.', 'Cooperation in language-teaching methodology and academic exchange.', 'Modern resources and joint seminars for foreign-language teaching.'],
        };
    }

    private function staffProfiles(): array
    {
        return [
            ['slug' => 'uzbek-foreign-languages-yusupova-shokhida-batirovna', 'names' => ['en' => 'Yusupova Shokhida Batirovna', 'uz' => 'Yusupova Shokhida Batirovna', 'ru' => 'Юсупова Шохида Батировна', 'ar' => 'يوسوبوفا شوخيدا باتيروفنا'], 'position' => 'Head of Department'],
            ['slug' => 'uzbek-foreign-languages-juraeva-malohat-mukhammedovna', 'names' => ['en' => 'Juraeva Malohat Mukhammedovna', 'uz' => 'Jurayeva Malohat Muhammedovna', 'ru' => 'Жураева Малохат Мухаммедовна', 'ar' => 'جوراييفا ملوحات محمدوفنا'], 'position' => 'Doctor of Philology, Professor'],
            ['slug' => 'uzbek-foreign-languages-yunusova-gulandom-samiyevna', 'names' => ['en' => 'Yunusova Gulandom Samiyevna', 'uz' => 'Yunusova Gulandom Samiyevna', 'ru' => 'Юнусова Гуландом Самиевна', 'ar' => 'يونوسوفا غولاندوم سامييفنا'], 'position' => 'Doctor of Philosophy, Professor'],
            ['slug' => 'uzbek-foreign-languages-kazimova-gulnora-hakimovna', 'names' => ['en' => 'Kazimova Gulnora Hakimovna', 'uz' => 'Kazimova Gulnora Hakimovna', 'ru' => 'Казимова Гулнора Хакимовна', 'ar' => 'كازيموفا غولنورا حكيموفنا'], 'position' => 'PhD, Associate Professor'],
            ['slug' => 'uzbek-foreign-languages-barotova-mubashira-barotovna', 'names' => ['en' => 'Barotova Mubashira Barotovna', 'uz' => 'Barotova Mubashira Barotovna', 'ru' => 'Баротова Мубашира Баротовна', 'ar' => 'باروتوفا مبشّرة باروتوفنا'], 'position' => 'PhD, Associate Professor'],
            ['slug' => 'uzbek-foreign-languages-muhamedjanova-sitora-jamalitdinovna', 'names' => ['en' => 'Muhamedjanova Sitora Jamalitdinovna', 'uz' => 'Muhamedjanova Sitora Jamalitdinovna', 'ru' => 'Мухамеджанова Ситора Джамалитдиновна', 'ar' => 'محمدجانوفا سيتورا جماليتدينوفنا'], 'position' => 'PhD, Associate Professor'],
            ['slug' => 'uzbek-foreign-languages-rajabova-marjona-ahmadovna', 'names' => ['en' => 'Rajabova Marjona Ahmadovna', 'uz' => 'Rajabova Marjona Ahmadovna', 'ru' => 'Ражабова Маржона Ахмадовна', 'ar' => 'رجبوفا مرجونا أحمدوفنا'], 'position' => 'PhD, Associate Professor'],
            ['slug' => 'uzbek-foreign-languages-alimova-nozima-rajabboyevna', 'names' => ['en' => 'Alimova Nozima Rajabboyevna', 'uz' => 'Alimova Nozima Rajabboyevna', 'ru' => 'Алимова Нозима Ражаббоевна', 'ar' => 'عليموفا نوزيما رجبوييفنا'], 'position' => 'Associate Professor'],
            ['slug' => 'uzbek-foreign-languages-narzullayeva-dilfuza-saitovna', 'names' => ['en' => 'Narzullayeva Dilfuza Saitovna', 'uz' => 'Narzullayeva Dilfuza Saitovna', 'ru' => 'Нарзуллаева Дилфуза Саитовна', 'ar' => 'نرزوللاييفا ديلفوزا سايتوفنا'], 'position' => 'PhD, Acting Associate Professor'],
            ['slug' => 'uzbek-foreign-languages-ibotova-nasiba-komilovna', 'names' => ['en' => 'Ibotova Nasiba Komilovna', 'uz' => 'Ibotova Nasiba Komilovna', 'ru' => 'Иботова Насиба Комиловна', 'ar' => 'إيبوتوفا نسيبة كوميلوفنا'], 'position' => 'PhD, Associate Professor'],
            ['slug' => 'uzbek-foreign-languages-nurmuradova-shahnoz-ibragimovna', 'names' => ['en' => 'Nurmuradova Shahnoz Ibragimovna', 'uz' => 'Nurmuradova Shahnoz Ibragimovna', 'ru' => 'Нурмурадова Шахноз Ибрагимовна', 'ar' => 'نورمرادوفا شاهنوز إبراهيموفنا'], 'position' => 'Associate Professor'],
            ['slug' => 'uzbek-foreign-languages-barakatova-dilorom-aminovna', 'names' => ['en' => 'Barakatova Dilorom Aminovna', 'uz' => 'Barakatova Dilorom Aminovna', 'ru' => 'Баракатова Дилором Аминовна', 'ar' => 'بركاتوفا ديلوروم أمينوفنا'], 'position' => 'Associate Professor'],
            ['slug' => 'uzbek-foreign-languages-kamolova-dilfuza-obidovna', 'names' => ['en' => 'Kamolova Dilfuza Obidovna', 'uz' => 'Kamolova Dilfuza Obidovna', 'ru' => 'Камолова Дилфуза Обидовна', 'ar' => 'كامولوفا ديلفوزا عبيدوفنا'], 'position' => 'Associate Professor'],
            ['slug' => 'uzbek-foreign-languages-saitova-komila-xasanboyevna', 'names' => ['en' => 'Saitova Komila Xasanboyevna', 'uz' => 'Saitova Komila Xasanboyevna', 'ru' => 'Саитова Комила Хасанбоевна', 'ar' => 'سايتوفا كاميلا حسنباييفنا'], 'position' => 'Associate Professor'],
            ['slug' => 'uzbek-foreign-languages-tillayeva-shahlo-maksudovna', 'names' => ['en' => 'Tillayeva Shahlo Maksudovna', 'uz' => 'Tillayeva Shahlo Maksudovna', 'ru' => 'Тиллаева Шахло Максудовна', 'ar' => 'تيلاييفا شاهلو مقصودوفنا'], 'position' => 'Associate Professor'],
            ['slug' => 'uzbek-foreign-languages-karamatova-zarina-fatilloyevna', 'names' => ['en' => 'Karamatova Zarina Fatilloyevna', 'uz' => 'Karamatova Zarina Fatilloyevna', 'ru' => 'Караматова Зарина Фатиллоевна', 'ar' => 'كراماتوفا زارينا فتيللاييفنا'], 'position' => 'PhD, Associate Professor'],
            ['slug' => 'uzbek-foreign-languages-salomova-malika-zohirovna', 'names' => ['en' => 'Salomova Malika Zohirovna', 'uz' => 'Salomova Malika Zohirovna', 'ru' => 'Саломова Малика Зохировна', 'ar' => 'سالوموفا مليكة زوهيروفنا'], 'position' => 'Associate Professor'],
            ['slug' => 'uzbek-foreign-languages-kazakova-dilora-gaffarovna', 'names' => ['en' => 'Kazakova Dilora Gaffarovna', 'uz' => 'Kazakova Dilora Gaffarovna', 'ru' => 'Казакова Дилора Гаффаровна', 'ar' => 'كازاكوفا ديلورا غفاروفنا'], 'position' => 'Associate Professor'],
            ['slug' => 'uzbek-foreign-languages-shoyimqulova-mahzuna-shavkatovna', 'names' => ['en' => 'Shoyimqulova Mahzuna Shavkatovna', 'uz' => 'Shoyimqulova Mahzuna Shavkatovna', 'ru' => 'Шойимкулова Махзуна Шавкатовна', 'ar' => 'شوييمقولوفا محزونا شوكتوفنا'], 'position' => 'Associate Professor'],
            ['slug' => 'uzbek-foreign-languages-axmedova-gulshod-umarovna', 'names' => ['en' => 'Axmedova Gulshod Umarovna', 'uz' => 'Axmedova Gulshod Umarovna', 'ru' => 'Ахмедова Гулшод Умаровна', 'ar' => 'أحمدوفا غولشود عمروفنا'], 'position' => 'Associate Professor'],
            ['slug' => 'uzbek-foreign-languages-tsukanova-yelena-nikolayevna', 'names' => ['en' => 'Tsukanova Yelena Nikolayevna', 'uz' => 'Tsukanova Yelena Nikolayevna', 'ru' => 'Цуканова Елена Николаевна', 'ar' => 'تسوكانوفا يلينا نيكولايفنا'], 'position' => 'PhD'],
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
            'Head of Department' => ['en' => 'Head of Department', 'uz' => 'Kafedra mudiri', 'ru' => 'Заведующая кафедрой', 'ar' => 'رئيسة القسم'],
            'Doctor of Philology, Professor' => ['en' => 'Doctor of Philology, Professor', 'uz' => 'Filologiya fanlari doktori, professor', 'ru' => 'Доктор филологических наук, профессор', 'ar' => 'دكتورة في فقه اللغة، أستاذة'],
            'Doctor of Philosophy, Professor' => ['en' => 'Doctor of Philosophy, Professor', 'uz' => 'Falsafa fanlari doktori, professor', 'ru' => 'Доктор философских наук, профессор', 'ar' => 'دكتورة في الفلسفة، أستاذة'],
            'PhD, Associate Professor' => ['en' => 'PhD, Associate Professor', 'uz' => 'PhD, dotsent', 'ru' => 'PhD, доцент', 'ar' => 'دكتوراه، أستاذة مشاركة'],
            'Associate Professor' => ['en' => 'Associate Professor', 'uz' => 'Dotsent', 'ru' => 'Доцент', 'ar' => 'أستاذة مشاركة'],
            'PhD, Acting Associate Professor' => ['en' => 'PhD, Acting Associate Professor', 'uz' => 'PhD, dotsent v.b.', 'ru' => 'PhD, и.о. доцента', 'ar' => 'دكتوراه، أستاذة مشاركة بالإنابة'],
            'PhD' => ['en' => 'PhD', 'uz' => 'PhD', 'ru' => 'PhD', 'ar' => 'دكتوراه'],
        ];

        return $map[$position][$locale] ?? $position;
    }

    private function bio(string $name, string $position, string $locale): string
    {
        return match ($locale) {
            'uz' => "{$name} O‘zbek va xorijiy tillar kafedrasida {$position} sifatida faoliyat yuritadi.",
            'ru' => "{$name} работает на кафедре узбекского и иностранных языков в должности «{$position}».",
            'ar' => "{$name} تعمل في قسم اللغات الأوزبكية والأجنبية بصفة {$position}.",
            default => "{$name} serves as {$position} in the Department of Uzbek and Foreign Languages.",
        };
    }

    private function label(string $locale, string $key): string
    {
        $labels = [
            'overview' => ['en' => 'About the Department', 'uz' => 'Kafedra haqida', 'ru' => 'О кафедре', 'ar' => 'عن القسم'],
            'programs' => ['en' => 'Prepared Specialists', 'uz' => 'Tayyorlanadigan yo‘nalishlar', 'ru' => 'Подготавливаемые направления', 'ar' => 'المجالات التي يخدمها القسم'],
            'subjects' => ['en' => 'Taught Subjects', 'uz' => 'Kafedrada o‘qitiladigan fanlar', 'ru' => 'Преподаваемые дисциплины', 'ar' => 'المواد التي تدرس في القسم'],
            'staff' => ['en' => 'Professor-Teachers of the Department', 'uz' => 'Kafedra professor-o‘qituvchilari', 'ru' => 'Профессорско-преподавательский состав кафедры', 'ar' => 'أعضاء هيئة التدريس في القسم'],
            'research' => ['en' => 'Ongoing Research', 'uz' => 'Joriy ilmiy tadqiqotlar', 'ru' => 'Текущие исследования', 'ar' => 'الأبحاث الجارية'],
            'cooperation' => ['en' => 'Cooperation / International Relations', 'uz' => 'Hamkorlik / xalqaro aloqalar', 'ru' => 'Сотрудничество / международные связи', 'ar' => 'التعاون / العلاقات الدولية'],
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

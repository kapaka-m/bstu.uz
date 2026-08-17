<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('departments') || ! Schema::hasTable('department_translations')) {
            return;
        }

        $departmentId = DB::table('departments')->where('slug', 'irrigation-melioration')->value('id');

        if (! $departmentId) {
            return;
        }

        DB::transaction(function () use ($departmentId) {
            $this->normalizeDepartmentSections((int) $departmentId);
            $this->normalizeStaff((int) $departmentId);
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function normalizeDepartmentSections(int $departmentId): void
    {
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

            $sections = $this->replaceSection($sections, [
                'key' => 'research',
                'title' => $this->label($locale, 'research'),
                'items' => $this->researchItems(),
            ]);

            $sections = $this->replaceSection($sections, [
                'key' => 'cooperation',
                'title' => $this->label($locale, 'cooperation'),
                'items' => $this->cooperationItems($locale),
            ]);

            $sections = $this->replaceSection($sections, [
                'key' => 'staff',
                'title' => $this->label($locale, 'staff'),
                'items' => $this->staffSectionItems($locale),
            ]);

            DB::table('department_translations')
                ->where('id', $translation->id)
                ->update([
                    'content_sections' => json_encode(array_values($sections), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => now(),
                ]);
        }
    }

    private function replaceSection(array $sections, array $replacement): array
    {
        foreach ($sections as $index => $section) {
            if (($section['key'] ?? null) === $replacement['key']) {
                $sections[$index] = $replacement;

                return $sections;
            }
        }

        $sections[] = $replacement;

        return $sections;
    }

    private function normalizeStaff(int $departmentId): void
    {
        $deleteSlugs = [
            'irrigation-melioration-phd-kata-is-a-teacher',
            'irrigation-melioration-doctor-of-agricultural-sciences-dsc',
            'irrigation-melioration-kata-is-a-teacher',
            'irrigation-melioration-0-prof-jaxongir-m-mahmudov',
            'irrigation-melioration-1-dr-farida-a-nematova',
            'irrigation-melioration-2-umid-sh-gadoev',
        ];

        $deleteIds = DB::table('staff_profiles')
            ->whereIn('slug', $deleteSlugs)
            ->pluck('id')
            ->all();

        if ($deleteIds !== []) {
            DB::table('staff_profile_translations')->whereIn('staff_profile_id', $deleteIds)->delete();
            DB::table('staff_profiles')->whereIn('id', $deleteIds)->delete();
        }

        foreach ($this->staffProfiles() as $index => $profile) {
            $profileId = DB::table('staff_profiles')->where('slug', $profile['slug'])->value('id');

            if (! $profileId) {
                continue;
            }

            DB::table('staff_profiles')
                ->where('id', $profileId)
                ->update([
                    'department_id' => $departmentId,
                    'sort_order' => $index + 10,
                    'is_active' => true,
                    'updated_at' => now(),
                ]);

            foreach ($this->locales() as $locale) {
                $position = $this->localizedPosition($profile['position'], $locale);
                DB::table('staff_profile_translations')->updateOrInsert(
                    ['staff_profile_id' => $profileId, 'locale' => $locale],
                    [
                        'full_name' => $profile['names'][$locale] ?? $profile['names']['en'],
                        'position' => $position,
                        'bio' => $this->localizedBio($profile['names'][$locale] ?? $profile['names']['en'], $position, $locale),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    private function researchItems(): array
    {
        return [
            'Murodov, O.U., Juraev, A.K., Khamidov, M.Kh. Effectiveness of cost-effective irrigation technologies in cultivation of winter wheat in saline soils. AIP Conference Proceedings, 3256(1).',
            'Муродов О.У., Исаев С.Х. Mathematical model program for assessing the effect of irrigating repeated crops with drainage water on the reclamation condition of lands. Certificate No. DGU 35001, Ministry of Justice of the Republic of Uzbekistan, registered on 15.03.2024.',
            'Муродов О.У., Исаев С.Х. Recommendations on assessing the effect of irrigating repeated crops with low-mineralized drainage water on the reclamation condition of saline soils. Bukhara, 2024, 32 p.',
            'Муродов О.У., Исаев С.Х. Mathematical model program for assessing the effect of irrigating repeated crops with drainage water on yield. Certificate No. DGU 35000, Ministry of Justice of the Republic of Uzbekistan, registered on 15.03.2024.',
            'Murodov O.U. Effects of repeated crops irrigated with low-mineralized drainage water, fertilizer rates, and biopreparations on volumetric mass of soil. Academic Research in Modern Science, Washington, USA, 24 October 2024, pp. 186-189.',
            'Murodov O.U., Isaev S.Kh. Assessment of the efficiency of bioprepare in irrigation with low mineralization ditch water. European Journal of Agricultural and Rural Education, Vol. 5, 2024, pp. 13-14.',
            'Khamidov M.K., Balla D., Hamidov A.M. Collector-drainage water in saline and arid irrigation areas for adaptation to climate change. IOP Conference Series: Earth and Environmental Science, 422(1), 012121, 2020.',
            'Khamidov, M., Juraev, A., Atamuradov, B., Rustamova, K., Najmiddinov, A., & Nurbekov, A. Effects of deep softener and chemical compounds on mechanical compositions in heavy, difficult-to-ameliorate soils. IOP Conference Series: Earth and Environmental Science, Vol. 1068, 012017, 2022.',
            'Khamidov M.K. et al. Efficiency of drip irrigation technology of cotton in the saline soils of Bukhara oasis. BIO Web of Conferences, Vol. 103, 00019, 2024.',
            'Khamidov M.Kh., Buriev X.B., Juraev A.K., Sharifov F.K., Isabaev K.T. Efficiency of drip irrigation technology of cotton in saline soils of Bukhara oasis. IOP Conference Series: Earth and Environmental Science, Vol. 1138, 012007, 2023.',
            'Хамидов М.Х. Influence of phytoremediation plants on soil salts. Innovative Technologies in Water Management Complex, Rovno, Ukraine, 2012, pp. 32-34.',
            'Juraev A.K. et al. Effectiveness of cost-effective irrigation technologies in cultivation of winter wheat in saline soils. AIP Conference Proceedings, Vol. 3256, No. 1, 050039, 2025.',
            'Juraev A.Q., Juraev U.A., Murodov O.U., Atamuradov B.N., Najmiddinov M.M., Ruziyeva M.A. Investigating irrigation system by using drainage water in the cultivation of repeated millet crop. BIO Web of Conferences, Vol. 103, 00014, 2024.',
            'Juraev A.K., Khamidov M.K., Atamuradov B.N., Murodov O.U., Rustamova K.B., Najmiddinov M.M. Effect of deep softeners on irrigation, salt washing and cotton yield on heavy-textured soils with difficult meliorative status. IOP Conference Series: Earth and Environmental Science, Vol. 1138, 012006, 2023.',
        ];
    }

    private function cooperationItems(string $locale): array
    {
        return match ($locale) {
            'uz' => [
                '2017-2021 yillarda O‘zbekiston Respublikasini rivojlantirishning beshta ustuvor yo‘nalishi bo‘yicha Harakatlar strategiyasi davlat dasturida belgilangan vazifalarni bajarish maqsadida professor Ahmad Muhammadxonovich Khamidov kafedraga jalb etilgan va talabalar hamda magistrantlarga ma’ruzalar o‘qib kelmoqda.',
                'Irrigatsiya va melioratsiya kafedrasi doktoranti Maruf Rustamovich Ulugov Erasmus+ Student Short-Term Mobility for Traineeship loyihasi doirasida Slovakiya Respublikasidagi UNIVERSITY OF PRESOV Rectorate International Relations Office’da stajirovka o‘tagan.',
                '2025-2026 o‘quv yilida kafedraning 4 nafar professor-o‘qituvchisi Istanbul Technical University’da qisqa muddatli stajirovka o‘tashi va ta’lim jarayonini raqamlashtirish hamda raqamli ta’lim texnologiyalarini keng joriy etish bo‘yicha hamkorlik memorandumi imzolanishi rejalashtirilgan.',
            ],
            'ru' => [
                'В целях выполнения задач государственной программы по реализации Стратегии действий по пяти приоритетным направлениям развития Республики Узбекистан на 2017-2021 годы профессор Ахмад Мухаммадханович Хамидов был привлечен к работе кафедры и проводит занятия для студентов и магистрантов.',
                'Докторант кафедры ирригации и мелиорации Маруф Рустамович Улугов прошел стажировку в UNIVERSITY OF PRESOV Rectorate International Relations Office в Словацкой Республике в рамках проекта Erasmus+ Student Short-Term Mobility for Traineeship.',
                'В 2025-2026 учебном году планируется краткосрочная стажировка 4 преподавателей кафедры в Istanbul Technical University, а также подписание меморандума о сотрудничестве по цифровизации высшего образования и широкому внедрению цифровых образовательных технологий.',
            ],
            'ar' => [
                'في إطار تنفيذ مهام البرنامج الحكومي لاستراتيجية العمل في المجالات الخمسة ذات الأولوية لتطوير جمهورية أوزبكستان للفترة 2017-2021، تم إشراك الأستاذ أحمد محمدخانوفِتش خاميدوف في أنشطة القسم، حيث يقدم محاضرات للطلاب وطلبة الماجستير.',
                'أجرى طالب الدكتوراه في قسم الري واستصلاح الأراضي ماروف رستاموفيتش أولوغوف تدريبا في مكتب العلاقات الدولية برئاسة جامعة بريشوف في جمهورية سلوفاكيا ضمن مشروع Erasmus+ Student Short-Term Mobility for Traineeship.',
                'خلال العام الأكاديمي 2025-2026، من المخطط أن يشارك 4 من أعضاء هيئة التدريس بالقسم في تدريب قصير الأجل في Istanbul Technical University، مع توقيع مذكرة تعاون لدعم رقمنة التعليم العالي وتوسيع استخدام تقنيات التعليم الرقمية.',
            ],
            default => [
                'To support the tasks of the State Program for the Action Strategy on five priority areas of development of the Republic of Uzbekistan for 2017-2021, Professor Ahmad Mukhamadkhanovich Khamidov was engaged by the department and delivers lectures to students and master’s students.',
                'Maruf Rustamovich Ulugov, a doctoral student of the Department of Irrigation and Land Reclamation, completed an internship at the UNIVERSITY OF PRESOV Rectorate International Relations Office in Slovakia within the Erasmus+ Student Short-Term Mobility for Traineeship project.',
                'In the 2025-2026 academic year, 4 faculty members of the department are planned to complete short-term training at Istanbul Technical University, with a cooperation memorandum planned to support higher education digitalization and wider use of digital educational technologies.',
            ],
        };
    }

    private function staffProfiles(): array
    {
        return [
            ['slug' => 'irrigation-melioration-murodov-otabek-ulugbekovich', 'names' => ['en' => 'Murodov Otabek Ulugbekovich', 'uz' => 'Murodov Otabek Ulug‘bekovich', 'ru' => 'Муродов Отабек Улугбекович', 'ar' => 'مورودوف أوتابيك أولوغبيكوفيتش'], 'position' => 'Head of Department'],
            ['slug' => 'irrigation-melioration-inoyatov-ikrom-shakhrullayevich', 'names' => ['en' => 'Inoyatov Ikrom Shakhrullayevich', 'uz' => 'Inoyatov Ikrom Shahrullayevich', 'ru' => 'Иноятов Икром Шахруллаевич', 'ar' => 'إينوياتوف إكروم شخرولايفيتش'], 'position' => 'PhD, Senior Lecturer'],
            ['slug' => 'irrigation-melioration-khamidov-mukhammadkhan', 'names' => ['en' => 'Khamidov Mukhammadkhan', 'uz' => 'Khamidov Muhammadxon', 'ru' => 'Хамидов Мухаммадхан', 'ar' => 'خاميدوف محمدخان'], 'position' => 'Doctor of Agricultural Sciences, Professor'],
            ['slug' => 'irrigation-melioration-nurov-dilmurod-elmurodovich', 'names' => ['en' => 'Nurov Dilmurod Elmurodovich', 'uz' => 'Nurov Dilmurod Elmurodovich', 'ru' => 'Нуров Дилмурод Элмуродович', 'ar' => 'نوروف ديلمورود إلمورودوفيتش'], 'position' => 'PhD'],
            ['slug' => 'irrigation-melioration-jorayev-anvar-kurbonovich', 'names' => ['en' => 'Jorayev Anvar Kurbonovich', 'uz' => 'Jo‘rayev Anvar Qurbonovich', 'ru' => 'Жораев Анвар Курбонович', 'ar' => 'جوراييف أنور قربونوفيتش'], 'position' => 'Doctor of Agricultural Sciences (DSc)'],
            ['slug' => 'irrigation-melioration-hamidov-akhmad-mukhammedkhanovich', 'names' => ['en' => 'Hamidov Akhmad Mukhammedkhanovich', 'uz' => 'Hamidov Ahmad Muhammadxonovich', 'ru' => 'Хамидов Ахмад Мухаммадханович', 'ar' => 'حميدوف أحمد محمدخانوفِتش'], 'position' => 'Doctor of Agricultural Sciences (DSc)'],
            ['slug' => 'irrigation-melioration-khamrayev-kamol-shukhratovich-in-agricultural-sciences', 'names' => ['en' => 'Khamrayev Kamol Shukhratovich', 'uz' => 'Khamrayev Kamol Shukhratovich', 'ru' => 'Хамраев Камол Шухратович', 'ar' => 'خمرائيف كامول شوخراتوفيتش'], 'position' => 'PhD in Agricultural Sciences'],
            ['slug' => 'irrigation-melioration-in-agricultural-sciences-kadyrov-zayniddin-zaripovich', 'names' => ['en' => 'Kadyrov Zayniddin Zaripovich', 'uz' => 'Kadyrov Zayniddin Zaripovich', 'ru' => 'Кадыров Зайниддин Зарипович', 'ar' => 'قاديروف زين الدين زاريبوفيتش'], 'position' => 'PhD in Agricultural Sciences'],
            ['slug' => 'irrigation-melioration-isoyeva-laylo-bakhtiyorovna', 'names' => ['en' => 'Isoyeva Laylo Bakhtiyorovna', 'uz' => 'Isoyeva Laylo Baxtiyorovna', 'ru' => 'Исоева Лайло Бахтиёровна', 'ar' => 'إيسوييفا لايلو بختيوروفنا'], 'position' => 'PhD'],
            ['slug' => 'irrigation-melioration-in-agricultural-sciences-atamurodov-behruz-nemat-oglu', 'names' => ['en' => "Atamurodov Behruz Ne'mat oglu", 'uz' => 'Atamurodov Behruz Ne’mat o‘g‘li', 'ru' => 'Атамуродов Бехруз Неъмат угли', 'ar' => 'أتامورودوف بهروز نعمت أوغلي'], 'position' => 'PhD in Agricultural Sciences'],
            ['slug' => 'irrigation-melioration-davronov-wave-farmonovich', 'names' => ['en' => 'Davronov Tolqin Farmonovich', 'uz' => 'Davronov To‘lqin Farmonovich', 'ru' => 'Давронов Тулкин Фармонович', 'ar' => 'دافرونوف تولقين فرمانوفيتش'], 'position' => 'Senior Lecturer'],
            ['slug' => 'irrigation-melioration-boriev-khurshid-bahodirovich', 'names' => ['en' => 'Boriev Khurshid Bahodirovich', 'uz' => 'Bo‘riyev Xurshid Bahodirovich', 'ru' => 'Бориев Хуршид Баходирович', 'ar' => 'بورييف خورشيد بهاديروفيتش'], 'position' => 'Associate Professor'],
            ['slug' => 'irrigation-melioration-turayev-ulugbek-utkirovich', 'names' => ['en' => 'Turayev Ulugbek Utkirovich', 'uz' => 'Turayev Ulug‘bek O‘tkir o‘g‘li', 'ru' => 'Тураев Улугбек Уткирович', 'ar' => 'توراييف أولوغبيك أوتكيروفيتش'], 'position' => 'Assistant'],
        ];
    }

    private function staffSectionItems(string $locale): array
    {
        return collect($this->staffProfiles())
            ->map(function (array $profile) use ($locale) {
                $name = $profile['names'][$locale] ?? $profile['names']['en'];
                $position = $this->localizedPosition($profile['position'], $locale);

                return "{$name}\n{$position}";
            })
            ->all();
    }

    private function localizedPosition(string $position, string $locale): string
    {
        $positions = [
            'Head of Department' => ['en' => 'Head of Department', 'uz' => 'Kafedra mudiri', 'ru' => 'Заведующий кафедрой', 'ar' => 'رئيس القسم'],
            'PhD, Senior Lecturer' => ['en' => 'PhD, Senior Lecturer', 'uz' => 'PhD, katta o‘qituvchi', 'ru' => 'PhD, старший преподаватель', 'ar' => 'دكتوراه، محاضر أول'],
            'Doctor of Agricultural Sciences, Professor' => ['en' => 'Doctor of Agricultural Sciences, Professor', 'uz' => 'Qishloq xo‘jaligi fanlari doktori, professor', 'ru' => 'Доктор сельскохозяйственных наук, профессор', 'ar' => 'دكتور في العلوم الزراعية، أستاذ'],
            'PhD' => ['en' => 'Doctor of Philosophy (PhD)', 'uz' => 'Falsafa doktori (PhD)', 'ru' => 'Доктор философии (PhD)', 'ar' => 'دكتوراه (PhD)'],
            'Doctor of Agricultural Sciences (DSc)' => ['en' => 'Doctor of Agricultural Sciences (DSc)', 'uz' => 'Qishloq xo‘jaligi fanlari doktori (DSc)', 'ru' => 'Доктор сельскохозяйственных наук (DSc)', 'ar' => 'دكتور في العلوم الزراعية (DSc)'],
            'PhD in Agricultural Sciences' => ['en' => 'Doctor of Philosophy (PhD) in Agricultural Sciences', 'uz' => 'Qishloq xo‘jaligi fanlari bo‘yicha falsafa doktori (PhD)', 'ru' => 'Доктор философии (PhD) по сельскохозяйственным наукам', 'ar' => 'دكتوراه في العلوم الزراعية'],
            'Senior Lecturer' => ['en' => 'Senior Lecturer', 'uz' => 'Katta o‘qituvchi', 'ru' => 'Старший преподаватель', 'ar' => 'محاضر أول'],
            'Associate Professor' => ['en' => 'Associate Professor', 'uz' => 'Dotsent', 'ru' => 'Доцент', 'ar' => 'أستاذ مشارك'],
            'Assistant' => ['en' => 'Assistant', 'uz' => 'Assistent', 'ru' => 'Ассистент', 'ar' => 'مساعد'],
        ];

        return $positions[$position][$locale] ?? $position;
    }

    private function localizedBio(string $name, string $position, string $locale): string
    {
        return match ($locale) {
            'uz' => "{$name} Irrigatsiya va melioratsiya kafedrasida {$position} sifatida faoliyat yuritadi.",
            'ru' => "{$name} работает на кафедре ирригации и мелиорации в должности «{$position}».",
            'ar' => "{$name} يعمل/تعمل في قسم الري واستصلاح الأراضي بصفة {$position}.",
            default => "{$name} serves as {$position} in the Department of Irrigation and Land Reclamation.",
        };
    }

    private function label(string $locale, string $key): string
    {
        $labels = [
            'research' => ['en' => 'Research Work', 'uz' => 'Ilmiy-tadqiqot ishlari', 'ru' => 'Научно-исследовательская работа', 'ar' => 'الأعمال البحثية'],
            'cooperation' => ['en' => 'International Cooperation', 'uz' => 'Xalqaro hamkorlik', 'ru' => 'Международное сотрудничество', 'ar' => 'التعاون الدولي'],
            'staff' => ['en' => 'Professor-Teachers of the Department', 'uz' => 'Kafedra professor-o‘qituvchilari', 'ru' => 'Профессорско-преподавательский состав кафедры', 'ar' => 'أعضاء هيئة التدريس في القسم'],
        ];

        return $labels[$key][$locale] ?? $labels[$key]['en'];
    }

    private function locales(): array
    {
        if (! Schema::hasTable('locales')) {
            return ['en', 'uz', 'ru', 'ar'];
        }

        return DB::table('locales')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter()
            ->values()
            ->all() ?: ['en', 'uz', 'ru', 'ar'];
    }
};

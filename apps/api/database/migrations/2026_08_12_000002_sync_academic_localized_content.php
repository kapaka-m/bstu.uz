<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    private array $locales = ['en', 'uz', 'ru', 'ar'];

    public function up(): void
    {
        $this->syncFaculties();
        $this->syncDepartments();
        $this->syncPrograms();
        $this->syncStaffProfiles();

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function syncFaculties(): void
    {
        foreach (DB::table('faculties')->get() as $faculty) {
            $en = $this->translation('faculty_translations', 'faculty_id', $faculty->id, 'en');
            $baseName = $en->name ?? $this->humanize($faculty->slug);
            $names = $this->facultyNames()[$faculty->slug] ?? $this->translatedNameSet($baseName);

            foreach ($this->locales as $locale) {
                $name = $locale === 'en' ? $baseName : ($names[$locale] ?? $this->phrase($baseName, $locale));
                $description = $this->facultyDescription($faculty->slug, $name, $locale, $en->description ?? '');
                $contentSections = $this->translateJsonText($en->content_sections ?? null, $locale);

                DB::table('faculty_translations')->updateOrInsert(
                    ['faculty_id' => $faculty->id, 'locale' => $locale],
                    [
                        'name' => $name,
                        'short_name' => $name,
                        'description' => $description,
                        'content_sections' => $contentSections,
                        'meta_title' => $name,
                        'meta_description' => Str::limit($description, 240, ''),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    private function syncDepartments(): void
    {
        foreach (DB::table('departments')->get() as $department) {
            $en = $this->translation('department_translations', 'department_id', $department->id, 'en');
            $baseName = $en->name ?? $this->humanize($department->slug);
            $names = $this->departmentNames()[$department->slug] ?? $this->translatedNameSet($baseName);

            foreach ($this->locales as $locale) {
                $name = $locale === 'en' ? $baseName : ($names[$locale] ?? $this->phrase($baseName, $locale));
                $description = $this->departmentDescription($name, $locale, $en->description ?? '');

                DB::table('department_translations')->updateOrInsert(
                    ['department_id' => $department->id, 'locale' => $locale],
                    [
                        'name' => $name,
                        'short_name' => $name,
                        'description' => $description,
                        'content_sections' => $this->translateJsonText($en->content_sections ?? null, $locale),
                        'meta_title' => $name,
                        'meta_description' => Str::limit($description, 240, ''),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    private function syncPrograms(): void
    {
        $programs = DB::table('programs')
            ->leftJoin('faculties', 'programs.faculty_id', '=', 'faculties.id')
            ->leftJoin('departments', 'programs.department_id', '=', 'departments.id')
            ->select('programs.*', 'faculties.slug as faculty_slug', 'departments.slug as department_slug')
            ->get();

        foreach ($programs as $program) {
            $en = $this->translation('program_translations', 'program_id', $program->id, 'en');
            $baseName = $en->name ?? $this->humanize($program->slug);
            $names = $this->programNames()[$baseName] ?? $this->translatedNameSet($baseName);
            $degree = $program->degree ?: 'bachelor';
            $duration = (int) ($program->duration_years ?: 4);

            foreach ($this->locales as $locale) {
                $name = $locale === 'en' ? $baseName : ($names[$locale] ?? $this->phrase($baseName, $locale));
                $facultyName = $this->localizedFacultyName($program->faculty_slug, $locale);
                $departmentName = $this->localizedDepartmentName($program->department_slug, $locale);
                $description = $this->programDescription($name, $facultyName, $departmentName, $degree, $duration, $locale);

                DB::table('program_translations')->updateOrInsert(
                    ['program_id' => $program->id, 'locale' => $locale],
                    [
                        'name' => $name,
                        'description' => $description,
                        'requirements' => $this->programRequirements($locale),
                        'documents' => $this->programDocuments($locale),
                        'curriculum_summary' => $this->programCurriculum($duration, $locale),
                        'career_opportunities' => $this->programCareers($facultyName, $departmentName, $locale),
                        'meta_title' => $name,
                        'meta_description' => Str::limit($description, 240, ''),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    private function syncStaffProfiles(): void
    {
        $staff = DB::table('staff_profiles')
            ->leftJoin('faculties', 'staff_profiles.faculty_id', '=', 'faculties.id')
            ->leftJoin('departments', 'staff_profiles.department_id', '=', 'departments.id')
            ->select('staff_profiles.*', 'faculties.slug as faculty_slug', 'departments.slug as department_slug')
            ->get();

        foreach ($staff as $profile) {
            $en = $this->translation('staff_profile_translations', 'staff_profile_id', $profile->id, 'en');
            $fullName = $en->full_name ?? $this->humanize($profile->slug);
            $position = $en->position ?? 'Academic Staff';

            foreach ($this->locales as $locale) {
                DB::table('staff_profile_translations')->updateOrInsert(
                    ['staff_profile_id' => $profile->id, 'locale' => $locale],
                    [
                        'full_name' => $locale === 'ar' ? $this->arabicName($fullName) : $fullName,
                        'position' => $this->position($position, $locale),
                        'bio' => $this->staffBio($fullName, $position, $profile->faculty_slug, $profile->department_slug, $locale),
                        'office' => $this->office($en->office ?? $profile->reception_time ?? '', $locale),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    private function translation(string $table, string $fk, int|string $id, string $locale): ?object
    {
        return DB::table($table)->where($fk, $id)->where('locale', $locale)->first();
    }

    private function localizedFacultyName(?string $slug, string $locale): string
    {
        if (! $slug) {
            return $this->entityLabel('university', $locale);
        }

        return $this->facultyNames()[$slug][$locale]
            ?? DB::table('faculties')
                ->join('faculty_translations', 'faculties.id', '=', 'faculty_translations.faculty_id')
                ->where('faculties.slug', $slug)
                ->where('faculty_translations.locale', $locale)
                ->value('faculty_translations.name')
            ?? $this->humanize($slug);
    }

    private function localizedDepartmentName(?string $slug, string $locale): string
    {
        if (! $slug) {
            return $this->entityLabel('academic department', $locale);
        }

        return $this->departmentNames()[$slug][$locale]
            ?? DB::table('departments')
                ->join('department_translations', 'departments.id', '=', 'department_translations.department_id')
                ->where('departments.slug', $slug)
                ->where('department_translations.locale', $locale)
                ->value('department_translations.name')
            ?? $this->humanize($slug);
    }

    private function translatedNameSet(string $english): array
    {
        return [
            'en' => $english,
            'uz' => $this->phrase($english, 'uz'),
            'ru' => $this->phrase($english, 'ru'),
            'ar' => $this->phrase($english, 'ar'),
        ];
    }

    private function phrase(string $text, string $locale): string
    {
        if ($locale === 'en') {
            return $text;
        }

        $map = $this->phraseMap()[$locale] ?? [];
        uksort($map, fn ($a, $b) => strlen($b) <=> strlen($a));
        $translated = $text;
        $translated = $this->normalizeLocalizedSchedule($translated, $locale);
        foreach ($map as $from => $to) {
            $translated = str_ireplace($from, $to, $translated);
        }

        return trim($translated);
    }

    private function normalizeLocalizedSchedule(string $text, string $locale): string
    {
        $timePattern = '([0-2]?\d:[0-5]\d\s*[-–]\s*[0-2]?\d:[0-5]\d)';

        if (preg_match('/daily\s+'.$timePattern.'\s*\(?\s*except\s+monday\s+and\s+saturday\s*\)?/i', $text, $matches)) {
            return match ($locale) {
                'uz' => 'Har kuni '.$matches[1].' (dushanba va shanbadan tashqari)',
                'ru' => 'Ежедневно '.$matches[1].' (кроме понедельника и субботы)',
                'ar' => 'يومياً '.$matches[1].' (ما عدا الاثنين والسبت)',
                default => $text,
            };
        }

        if (preg_match('/daily\s+'.$timePattern.'/i', $text, $matches)) {
            return match ($locale) {
                'uz' => 'Har kuni '.$matches[1],
                'ru' => 'Ежедневно '.$matches[1],
                'ar' => 'يومياً '.$matches[1],
                default => $text,
            };
        }

        if (preg_match('/monday\s*[-–]\s*friday\s+'.$timePattern.'/i', $text, $matches)) {
            return match ($locale) {
                'uz' => 'Dushanba-juma '.$matches[1],
                'ru' => 'Понедельник-пятница '.$matches[1],
                'ar' => 'الاثنين-الجمعة '.$matches[1],
                default => $text,
            };
        }

        return $text;
    }

    private function translateJsonText(?string $json, string $locale): ?string
    {
        if (! $json) {
            return null;
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return $this->phrase($json, $locale);
        }

        $walker = function ($value) use (&$walker, $locale) {
            if (is_string($value)) {
                return $this->phrase($value, $locale);
            }
            if (is_array($value)) {
                return array_map($walker, $value);
            }

            return $value;
        };

        return json_encode($walker($decoded), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function facultyDescription(string $slug, string $name, string $locale, string $fallback): string
    {
        if ($locale === 'en') {
            return $fallback ?: "{$name} prepares qualified specialists through theoretical and practical education.";
        }

        return match ($locale) {
            'uz' => "{$name} zamonaviy sanoat, xizmat ko‘rsatish, muhandislik va ilmiy yo‘nalishlar uchun malakali mutaxassislar tayyorlaydi. Talabalar nazariy bilim, amaliy ko‘nikma, laboratoriya ishlari va innovatsion texnologiyalar asosida ta’lim oladilar.",
            'ru' => "{$name} готовит квалифицированных специалистов для современных отраслей промышленности, сервиса, инженерии и науки. Студенты получают теоретические знания, практические навыки, лабораторную подготовку и опыт работы с инновационными технологиями.",
            'ar' => "{$name} تُعد متخصصين مؤهلين للقطاعات الحديثة في الصناعة والخدمات والهندسة والعلوم. يكتسب الطلاب معرفة نظرية ومهارات عملية وتدريباً مخبرياً وخبرة في التقنيات المبتكرة.",
            default => $fallback,
        };
    }

    private function departmentDescription(string $name, string $locale, string $fallback): string
    {
        if ($locale === 'en') {
            return $fallback ?: "{$name} connects academic training with applied research and professional practice.";
        }

        return match ($locale) {
            'uz' => "{$name} kafedrasi o‘quv jarayonini amaliy tadqiqotlar, ishlab chiqarish tajribasi va zamonaviy kasbiy tayyorgarlik bilan bog‘laydi.",
            'ru' => "Кафедра «{$name}» объединяет учебный процесс с прикладными исследованиями, производственной практикой и современной профессиональной подготовкой.",
            'ar' => "يربط قسم {$name} التعليم الأكاديمي بالبحث التطبيقي والخبرة العملية والإعداد المهني الحديث.",
            default => $fallback,
        };
    }

    private function programDescription(string $name, string $faculty, string $department, string $degree, int $duration, string $locale): string
    {
        $degreeLabel = $this->degreeLabel($degree, $locale);

        return match ($locale) {
            'en' => "{$name} is a {$duration}-year {$degreeLabel} program in {$faculty}. It is connected with {$department} and combines theoretical study, practical training, laboratory work, and industry-oriented learning.",
            'uz' => "{$name} — {$faculty} tarkibidagi {$duration} yillik {$degreeLabel} dasturi. Dastur {$department} bilan bog‘langan bo‘lib, nazariy ta’lim, amaliy mashg‘ulotlar, laboratoriya ishlari va ishlab chiqarishga yo‘naltirilgan tayyorgarlikni birlashtiradi.",
            'ru' => "{$name} — {$duration}-летняя программа уровня {$degreeLabel} в составе {$faculty}. Программа связана с кафедрой «{$department}» и объединяет теоретическое обучение, практическую подготовку, лабораторные работы и отраслевую направленность.",
            'ar' => "{$name} هو برنامج {$degreeLabel} مدته {$duration} سنوات ضمن {$faculty}. يرتبط البرنامج بـ {$department} ويجمع بين الدراسة النظرية والتدريب العملي والعمل المخبري والتعليم الموجه لاحتياجات سوق العمل.",
            default => "{$name} is a {$duration}-year {$degreeLabel} program.",
        };
    }

    private function programRequirements(string $locale): string
    {
        return match ($locale) {
            'uz' => 'O‘rta ta’lim yoki unga tenglashtirilgan hujjat, ariza hujjatlari va universitet tomonidan belgilangan qabul talablari.',
            'ru' => 'Документ о среднем образовании или его эквивалент, пакет документов для поступления и требования приема, утвержденные университетом.',
            'ar' => 'شهادة التعليم الثانوي أو ما يعادلها، ووثائق التقديم، واستيفاء شروط القبول المعتمدة من الجامعة.',
            default => 'Secondary education certificate or equivalent, application documents, and admission requirements approved by the university.',
        };
    }

    private function programDocuments(string $locale): string
    {
        return match ($locale) {
            'uz' => 'Pasport, ta’lim to‘g‘risidagi hujjat, baholar ilovasi, fotosurat va talab etilgan ariza hujjatlari.',
            'ru' => 'Паспорт, документ об образовании, приложение с оценками, фотография и необходимые документы для подачи заявления.',
            'ar' => 'جواز السفر، شهادة التعليم، كشف الدرجات، الصورة الشخصية، والوثائق المطلوبة للتقديم.',
            default => 'Passport, education certificate, transcript, photo, and required application documents.',
        };
    }

    private function programCurriculum(int $duration, string $locale): string
    {
        return match ($locale) {
            'uz' => "O‘qish muddati: {$duration} yil. Dastur asosiy fanlar, mutaxassislik fanlari, amaliyot, laboratoriya ishlari va bitiruv loyihasini o‘z ichiga oladi.",
            'ru' => "Срок обучения: {$duration} года. Программа включает базовые дисциплины, профильные предметы, практику, лабораторные работы и выпускной проект.",
            'ar' => "مدة الدراسة: {$duration} سنوات. يشمل البرنامج مواد أساسية وتخصصية وتدريباً عملياً وعملاً مخبرياً ومشروع تخرج.",
            default => "Duration of study: {$duration} years. The program includes core subjects, specialization courses, practice, laboratory work, and a graduation project.",
        };
    }

    private function programCareers(string $faculty, string $department, string $locale): string
    {
        return match ($locale) {
            'uz' => "Bitiruvchilar {$department} yo‘nalishiga mos ishlab chiqarish korxonalari, xizmat ko‘rsatish tashkilotlari, loyiha guruhlari, davlat muassasalari va ilmiy-innovatsion loyihalarda ishlashlari mumkin.",
            'ru' => "Выпускники могут работать на предприятиях и в организациях, связанных с направлением «{$department}», а также в проектных группах, государственных учреждениях и научно-инновационных проектах.",
            'ar' => "يمكن للخريجين العمل في المؤسسات والشركات المرتبطة بمجال {$department}، إضافة إلى فرق المشاريع والجهات الحكومية والمشروعات البحثية والابتكارية.",
            default => "Graduates can work in organizations related to {$department}, including industry, services, project teams, public institutions, and research or innovation projects.",
        };
    }

    private function staffBio(string $name, string $position, ?string $facultySlug, ?string $departmentSlug, string $locale): string
    {
        $translatedPosition = $this->position($position, $locale);
        $unit = $departmentSlug
            ? $this->localizedDepartmentName($departmentSlug, $locale)
            : $this->localizedFacultyName($facultySlug, $locale);

        return match ($locale) {
            'uz' => "{$name} — {$unit} tarkibida {$translatedPosition} sifatida faoliyat yuritadi. U o‘quv, ilmiy-uslubiy va tashkiliy jarayonlarni rivojlantirishga hissa qo‘shadi.",
            'ru' => "{$name} работает в подразделении «{$unit}» в должности «{$translatedPosition}». Он/она участвует в развитии учебной, научно-методической и организационной деятельности.",
            'ar' => "{$this->arabicName($name)} يعمل/تعمل في {$unit} بصفة {$translatedPosition}. ويساهم/تساهم في تطوير العملية التعليمية والمنهجية والتنظيمية.",
            default => "{$name} serves as {$position} in {$unit}, contributing to academic, methodological, and organizational development.",
        };
    }

    private function position(string $position, string $locale): string
    {
        if ($locale === 'en') {
            return $position;
        }

        $normalized = strtolower(strip_tags(trim($position)));
        $positions = [
            'dean' => ['uz' => 'Dekan', 'ru' => 'Декан', 'ar' => 'عميد الكلية'],
            'head of department' => ['uz' => 'Kafedra mudiri', 'ru' => 'Заведующий кафедрой', 'ar' => 'رئيس القسم'],
            'deputy dean for academic affairs' => ['uz' => 'O‘quv ishlari bo‘yicha dekan o‘rinbosari', 'ru' => 'Заместитель декана по учебной работе', 'ar' => 'نائب العميد للشؤون الأكاديمية'],
            'deputy dean for youth affairs' => ['uz' => 'Yoshlar masalalari bo‘yicha dekan o‘rinbosari', 'ru' => 'Заместитель декана по делам молодежи', 'ar' => 'نائب العميد لشؤون الشباب'],
            'academic staff' => ['uz' => 'Akademik xodim', 'ru' => 'Преподаватель', 'ar' => 'عضو هيئة تدريس'],
        ];

        if (isset($positions[$normalized])) {
            return $positions[$normalized][$locale];
        }

        $patterns = [
            'vice rector for international cooperation' => ['uz' => 'Xalqaro hamkorlik bo‘yicha prorektor', 'ru' => 'Проректор по международному сотрудничеству', 'ar' => 'نائب رئيس الجامعة للتعاون الدولي'],
            'vice rector for finance' => ['uz' => 'Moliya va iqtisodiyot bo‘yicha prorektor', 'ru' => 'Проректор по финансово-экономическим вопросам', 'ar' => 'نائب رئيس الجامعة للشؤون المالية والاقتصادية'],
            'vice rector for research' => ['uz' => 'Ilmiy ishlar va innovatsiyalar bo‘yicha prorektor', 'ru' => 'Проректор по научной работе и инновациям', 'ar' => 'نائب رئيس الجامعة للبحث والابتكار'],
            'vice rector for academic' => ['uz' => 'O‘quv ishlari bo‘yicha prorektor', 'ru' => 'Проректор по учебной работе', 'ar' => 'نائب رئيس الجامعة للشؤون الأكاديمية'],
            'first vice rector' => ['uz' => 'Birinchi prorektor', 'ru' => 'Первый проректор', 'ar' => 'النائب الأول لرئيس الجامعة'],
            'vice rector' => ['uz' => 'Prorektor', 'ru' => 'Проректор', 'ar' => 'نائب رئيس الجامعة'],
            'deputy dean for student affairs' => ['uz' => 'Talabalar ishlari bo‘yicha dekan o‘rinbosari', 'ru' => 'Заместитель декана по работе со студентами', 'ar' => 'نائب العميد لشؤون الطلاب'],
            'deputy dean' => ['uz' => 'Dekan o‘rinbosari', 'ru' => 'Заместитель декана', 'ar' => 'نائب العميد'],
            'dean of the faculty' => ['uz' => 'Fakultet dekani', 'ru' => 'Декан факультета', 'ar' => 'عميد الكلية'],
            'head of the department' => ['uz' => 'Kafedra mudiri', 'ru' => 'Заведующий кафедрой', 'ar' => 'رئيس القسم'],
            'kafedra mudiri' => ['uz' => 'Kafedra mudiri', 'ru' => 'Заведующий кафедрой', 'ar' => 'رئيس القسم'],
            'head of department' => ['uz' => 'Kafedra mudiri', 'ru' => 'Заведующий кафедрой', 'ar' => 'رئيس القسم'],
            'associate professor' => ['uz' => 'Dotsent lavozimidagi o‘qituvchi', 'ru' => 'Доцент', 'ar' => 'أستاذ مشارك'],
            'dotsent' => ['uz' => 'Dotsent lavozimidagi o‘qituvchi', 'ru' => 'Доцент', 'ar' => 'أستاذ مشارك'],
            'docent' => ['uz' => 'Dotsent lavozimidagi o‘qituvchi', 'ru' => 'Доцент', 'ar' => 'أستاذ مشارك'],
            'dosent' => ['uz' => 'Dotsent lavozimidagi o‘qituvchi', 'ru' => 'Доцент', 'ar' => 'أستاذ مشارك'],
            'professor' => ['uz' => 'Professor-o‘qituvchi', 'ru' => 'Профессор', 'ar' => 'أستاذ'],
            'senior lecturer' => ['uz' => 'Katta o‘qituvchi lavozimidagi xodim', 'ru' => 'Старший преподаватель', 'ar' => 'محاضر أول'],
            'senior teacher' => ['uz' => 'Katta o‘qituvchi lavozimidagi xodim', 'ru' => 'Старший преподаватель', 'ar' => 'محاضر أول'],
            'katta' => ['uz' => 'Katta o‘qituvchi lavozimidagi xodim', 'ru' => 'Старший преподаватель', 'ar' => 'محاضر أول'],
            'lecturer' => ['uz' => 'O‘qituvchi', 'ru' => 'Преподаватель', 'ar' => 'محاضر'],
            'teacher' => ['uz' => 'O‘qituvchi', 'ru' => 'Преподаватель', 'ar' => 'مدرس'],
            'assistant' => ['uz' => 'Assistent-o‘qituvchi', 'ru' => 'Ассистент преподавателя', 'ar' => 'مدرس مساعد'],
            'assistent' => ['uz' => 'Assistent-o‘qituvchi', 'ru' => 'Ассистент преподавателя', 'ar' => 'مدرس مساعد'],
            'asistent' => ['uz' => 'Assistent-o‘qituvchi', 'ru' => 'Ассистент преподавателя', 'ar' => 'مدرس مساعد'],
            'trainee' => ['uz' => 'Stajyor-o‘qituvchi', 'ru' => 'Преподаватель-стажер', 'ar' => 'مدرس متدرب'],
            'stajyor' => ['uz' => 'Stajyor-o‘qituvchi', 'ru' => 'Преподаватель-стажер', 'ar' => 'مدرس متدرب'],
            'doctorant' => ['uz' => 'Doktorant', 'ru' => 'Докторант', 'ar' => 'باحث دكتوراه'],
            'doctoral' => ['uz' => 'Doktorant', 'ru' => 'Докторант', 'ar' => 'باحث دكتوراه'],
            'basic doctoral' => ['uz' => 'Tayanch doktorant', 'ru' => 'Базовый докторант', 'ar' => 'باحث دكتوراه أساسي'],
            'phd' => ['uz' => 'PhD ilmiy darajasiga ega o‘qituvchi', 'ru' => 'Преподаватель со степенью PhD', 'ar' => 'عضو هيئة تدريس بدرجة دكتوراه'],
            'dsc' => ['uz' => 'Fan doktori', 'ru' => 'Доктор наук', 'ar' => 'دكتور علوم'],
            'candidate' => ['uz' => 'Fan nomzodi', 'ru' => 'Кандидат наук', 'ar' => 'مرشح علوم'],
            'doctor of' => ['uz' => 'Fan doktori', 'ru' => 'Доктор наук', 'ar' => 'دكتور علوم'],
            'doctor' => ['uz' => 'Doktor', 'ru' => 'Доктор', 'ar' => 'دكتور'],
            'sports coach' => ['uz' => 'Sport murabbiyi', 'ru' => 'Спортивный тренер', 'ar' => 'مدرب رياضي'],
            'cabinet manager' => ['uz' => 'Kabinet mudiri', 'ru' => 'Заведующий кабинетом', 'ar' => 'مسؤول المكتب'],
            'instructor' => ['uz' => 'Instruktor-o‘qituvchi', 'ru' => 'Инструктор-преподаватель', 'ar' => 'مدرب أكاديمي'],
        ];

        foreach ($patterns as $needle => $labels) {
            if (str_contains($normalized, $needle)) {
                return $labels[$locale];
            }
        }

        return match ($locale) {
            'uz' => 'Professor-o‘qituvchi',
            'ru' => 'Профессорско-преподавательский состав',
            'ar' => 'عضو هيئة تدريس',
            default => $position,
        };
    }

    private function office(string $office, string $locale): string
    {
        if ($office === '') {
            return '';
        }

        if ($locale !== 'en') {
            $normalized = strtolower(trim($office));

            if (str_contains($normalized, 'main campus')) {
                return match ($locale) {
                    'uz' => 'Asosiy kampus, 1-bino',
                    'ru' => 'Главный кампус, корпус 1',
                    'ar' => 'الحرم الرئيسي، المبنى 1',
                    default => $office,
                };
            }

            if (str_contains($normalized, 'dean office')) {
                return match ($locale) {
                    'uz' => 'Dekanat',
                    'ru' => 'Деканат',
                    'ar' => 'مكتب العمادة',
                    default => $office,
                };
            }

            if (str_contains($normalized, 'tuesday-thursday')) {
                return match ($locale) {
                    'uz' => 'Seshanba-payshanba 10:00-13:00',
                    'ru' => 'Вторник-четверг 10:00-13:00',
                    'ar' => 'الثلاثاء-الخميس 10:00-13:00',
                    default => $office,
                };
            }

            if (str_contains($normalized, 'dushanba-juma')) {
                return match ($locale) {
                    'uz' => 'Dushanbadan jumagacha 14:00-16:00',
                    'ru' => 'Понедельник-пятница 14:00-16:00',
                    'ar' => 'الاثنين-الجمعة 14:00-16:00',
                    default => $office,
                };
            }

            if (str_contains($normalized, 'every day')) {
                $time = trim((string) Str::of($office)->after('Every day'));

                return match ($locale) {
                    'uz' => 'Har kuni '.$time,
                    'ru' => 'Ежедневно '.$time,
                    'ar' => 'يومياً '.$time,
                    default => $office,
                };
            }
        }

        return $this->phrase($office, $locale);
    }

    private function degreeLabel(string $degree, string $locale): string
    {
        $degree = strtolower($degree);
        return match ($locale) {
            'uz' => $degree === 'master' ? 'magistratura' : ($degree === 'doctorate' ? 'doktorantura' : 'bakalavriat'),
            'ru' => $degree === 'master' ? 'магистратура' : ($degree === 'doctorate' ? 'докторантура' : 'бакалавриат'),
            'ar' => $degree === 'master' ? 'ماجستير' : ($degree === 'doctorate' ? 'دكتوراه' : 'بكالوريوس'),
            default => $degree === 'master' ? 'master' : ($degree === 'doctorate' ? 'doctoral' : 'bachelor'),
        };
    }

    private function entityLabel(string $value, string $locale): string
    {
        return match ($locale) {
            'uz' => $value === 'university' ? 'universitet' : 'akademik kafedra',
            'ru' => $value === 'university' ? 'университет' : 'академическая кафедра',
            'ar' => $value === 'university' ? 'الجامعة' : 'القسم الأكاديمي',
            default => $value,
        };
    }

    private function humanize(string $slug): string
    {
        return Str::of($slug)->replace(['-', '_'], ' ')->title()->toString();
    }

    private function arabicName(string $name): string
    {
        return $this->nameMap()[$name] ?? $name;
    }

    private function facultyNames(): array
    {
        return [
            'faculty-of-engineering' => ['en' => 'Faculty of Engineering', 'uz' => 'Muhandislik fakulteti', 'ru' => 'Инженерный факультет', 'ar' => 'كلية الهندسة'],
            'faculty-of-technology' => ['en' => 'Faculty of Technology', 'uz' => 'Texnologiya fakulteti', 'ru' => 'Технологический факультет', 'ar' => 'كلية التكنولوجيا'],
            'faculty-of-natural-resources-management' => ['en' => 'Faculty of Natural Resources Management', 'uz' => 'Tabiiy resurslarni boshqarish fakulteti', 'ru' => 'Факультет управления природными ресурсами', 'ar' => 'كلية إدارة الموارد الطبيعية'],
            'faculty-of-service-and-digitalization' => ['en' => 'Faculty of Service and Digitalization', 'uz' => 'Servis va raqamlashtirish fakulteti', 'ru' => 'Факультет сервиса и цифровизации', 'ar' => 'كلية الخدمات والرقمنة'],
        ];
    }

    private function departmentNames(): array
    {
        return [
            'electrical-power-engineering' => ['uz' => 'Elektr energetikasi kafedrasi', 'ru' => 'Кафедра электроэнергетики', 'ar' => 'قسم الهندسة الكهربائية والطاقة'],
            'architecture' => ['uz' => 'Arxitektura kafedrasi', 'ru' => 'Кафедра архитектуры', 'ar' => 'قسم العمارة'],
            'civil-engineering' => ['uz' => 'Qurilish muhandisligi kafedrasi', 'ru' => 'Кафедра гражданского строительства', 'ar' => 'قسم الهندسة المدنية'],
            'light-industry-engineering-and-design' => ['uz' => 'Yengil sanoat muhandisligi va dizayn kafedrasi', 'ru' => 'Кафедра инженерии и дизайна легкой промышленности', 'ar' => 'قسم هندسة وتصميم الصناعات الخفيفة'],
            'mechanics-engineering-graphics' => ['uz' => 'Mexanika va muhandislik grafikasi kafedrasi', 'ru' => 'Кафедра механики и инженерной графики', 'ar' => 'قسم الميكانيكا والرسم الهندسي'],
            'technological-machines-equipment' => ['uz' => 'Texnologik mashinalar va jihozlar kafedrasi', 'ru' => 'Кафедра технологических машин и оборудования', 'ar' => 'قسم الآلات والمعدات التكنولوجية'],
            'oil-gas-refining-technology' => ['uz' => 'Neft va gazni qayta ishlash texnologiyasi kafedrasi', 'ru' => 'Кафедра технологии переработки нефти и газа', 'ar' => 'قسم تكنولوجيا تكرير النفط والغاز'],
            'food-technology-service' => ['uz' => 'Oziq-ovqat texnologiyasi va servis kafedrasi', 'ru' => 'Кафедра пищевой технологии и сервиса', 'ar' => 'قسم تكنولوجيا الأغذية والخدمات'],
            'chemical-technology' => ['uz' => 'Kimyoviy texnologiya kafedrasi', 'ru' => 'Кафедра химической технологии', 'ar' => 'قسم التكنولوجيا الكيميائية'],
            'agricultural-products-storage-oil-fat-technology' => ['uz' => 'Qishloq xo‘jaligi mahsulotlarini saqlash va moy-yog‘ texnologiyasi kafedrasi', 'ru' => 'Кафедра хранения сельхозпродукции и масложировой технологии', 'ar' => 'قسم تخزين المنتجات الزراعية وتكنولوجيا الزيوت والدهون'],
            'oil-gas-engineering-upstream-downstream' => ['uz' => 'Neft va gaz ishi kafedrasi', 'ru' => 'Кафедра нефтегазового дела', 'ar' => 'قسم هندسة النفط والغاز'],
            'metrology-standardization-quality-control' => ['uz' => 'Metrologiya va standartlashtirish kafedrasi', 'ru' => 'Кафедра метрологии и стандартизации', 'ar' => 'قسم المترولوجيا والتقييس'],
            'irrigation-melioration' => ['uz' => 'Irrigatsiya va melioratsiya kafedrasi', 'ru' => 'Кафедра ирригации и мелиорации', 'ar' => 'قسم الري واستصلاح الأراضي'],
            'hydrotechnical-structures-pump-stations' => ['uz' => 'Gidrotexnika inshootlari va nasos stansiyalari kafedrasi', 'ru' => 'Кафедра гидротехнических сооружений и насосных станций', 'ar' => 'قسم المنشآت الهيدروليكية ومحطات الضخ'],
            'agricultural-water-resources-engineering-technologies' => ['uz' => 'Qishloq va suv xo‘jaligi muhandislik texnologiyalari kafedrasi', 'ru' => 'Кафедра инженерных технологий сельского и водного хозяйства', 'ar' => 'قسم تقنيات هندسة الزراعة وإدارة المياه'],
            'land-resources-management-state-land-cadastres' => ['uz' => 'Yer resurslarini boshqarish va davlat kadastrlari kafedrasi', 'ru' => 'Кафедра управления земельными ресурсами и государственных кадастров', 'ar' => 'قسم إدارة الموارد الأرضية والسجل العقاري الحكومي'],
            'industrial-ecology-hydrogeology' => ['uz' => 'Sanoat ekologiyasi va gidrogeologiya kafedrasi', 'ru' => 'Кафедра промышленной экологии и гидрогеологии', 'ar' => 'قسم البيئة الصناعية والهيدروجيولوجيا'],
            'vehicle-engineering-automotive-transport-systems' => ['uz' => 'Transport vositalari muhandisligi kafedrasi', 'ru' => 'Кафедра транспортного машиностроения', 'ar' => 'قسم هندسة المركبات وأنظمة النقل'],
            'technological-processes-production-automation' => ['uz' => 'Texnologik jarayonlar va ishlab chiqarishni avtomatlashtirish kafedrasi', 'ru' => 'Кафедра автоматизации технологических процессов и производств', 'ar' => 'قسم أتمتة العمليات التكنولوجية والإنتاج'],
            'information-and-communication-technologies' => ['uz' => 'Axborot-kommunikatsiya texnologiyalari kafedrasi', 'ru' => 'Кафедра информационно-коммуникационных технологий', 'ar' => 'قسم تكنولوجيا المعلومات والاتصالات'],
            'economics-and-management' => ['uz' => 'Iqtisodiyot va menejment kafedrasi', 'ru' => 'Кафедра экономики и менеджмента', 'ar' => 'قسم الاقتصاد والإدارة'],
            'social-sciences-physical-culture' => ['uz' => 'Ijtimoiy fanlar va jismoniy madaniyat kafedrasi', 'ru' => 'Кафедра социальных наук и физической культуры', 'ar' => 'قسم العلوم الاجتماعية والثقافة البدنية'],
            'exact-sciences' => ['uz' => 'Aniq fanlar kafedrasi', 'ru' => 'Кафедра точных наук', 'ar' => 'قسم العلوم الدقيقة'],
            'uzbek-foreign-languages' => ['uz' => 'O‘zbek va xorijiy tillar kafedrasi', 'ru' => 'Кафедра узбекского и иностранных языков', 'ar' => 'قسم اللغات الأوزبكية والأجنبية'],
            'textile-materials-science' => ['uz' => 'To‘qimachilik materialshunosligi kafedrasi', 'ru' => 'Кафедра текстильного материаловедения', 'ar' => 'قسم علم مواد النسيج'],
        ];
    }

    private function programNames(): array
    {
        return [
            'Economics' => ['uz' => 'Iqtisodiyot', 'ru' => 'Экономика', 'ar' => 'الاقتصاد'],
            'Accounting' => ['uz' => 'Buxgalteriya hisobi', 'ru' => 'Бухгалтерский учет', 'ar' => 'المحاسبة'],
            'Finance and Financial Technologies' => ['uz' => 'Moliya va moliyaviy texnologiyalar', 'ru' => 'Финансы и финансовые технологии', 'ar' => 'المالية والتقنيات المالية'],
            'Management' => ['uz' => 'Menejment', 'ru' => 'Менеджмент', 'ar' => 'الإدارة'],
            'Marketing' => ['uz' => 'Marketing va bozor tadqiqotlari', 'ru' => 'Маркетинг', 'ar' => 'التسويق'],
            'Construction' => ['uz' => 'Qurilish', 'ru' => 'Строительство', 'ar' => 'البناء'],
            'Information Systems Security & Cybersecurity' => ['uz' => 'Axborot tizimlari xavfsizligi va kiberxavfsizlik', 'ru' => 'Безопасность информационных систем и кибербезопасность', 'ar' => 'أمن نظم المعلومات والأمن السيبراني'],
            'Materials Science' => ['uz' => 'Materialshunoslik', 'ru' => 'Материаловедение', 'ar' => 'علم المواد'],
            'Metallurgy' => ['uz' => 'Metallurgiya sohasi', 'ru' => 'Металлургия', 'ar' => 'علم المعادن'],
            'Reclamation Hydrogeology' => ['uz' => 'Meliorativ gidrogeologiya', 'ru' => 'Мелиоративная гидрогеология', 'ar' => 'الهيدروجيولوجيا الاستصلاحية'],
            'Information Systems and Technologies' => ['uz' => 'Axborot tizimlari va texnologiyalari', 'ru' => 'Информационные системы и технологии', 'ar' => 'نظم وتكنولوجيا المعلومات'],
            'Computer Engineering' => ['uz' => 'Kompyuter muhandisligi', 'ru' => 'Компьютерная инженерия', 'ar' => 'هندسة الحاسوب'],
            'Software Engineering' => ['uz' => 'Dasturiy injiniring', 'ru' => 'Программная инженерия', 'ar' => 'هندسة البرمجيات'],
            'Artificial Intelligence' => ['uz' => 'Sun’iy intellekt', 'ru' => 'Искусственный интеллект', 'ar' => 'الذكاء الاصطناعي'],
            'Cybersecurity Engineering' => ['uz' => 'Kiberxavfsizlik muhandisligi', 'ru' => 'Инженерия кибербезопасности', 'ar' => 'هندسة الأمن السيبراني'],
            'Automation of Technological Processes and Production' => ['uz' => 'Texnologik jarayonlar va ishlab chiqarishni avtomatlashtirish', 'ru' => 'Автоматизация технологических процессов и производств', 'ar' => 'أتمتة العمليات التكنولوجية والإنتاج'],
            'Mechatronics and Robotics' => ['uz' => 'Mexatronika va robototexnika', 'ru' => 'Мехатроника и робототехника', 'ar' => 'الميكاترونكس والروبوتات'],
            'Tourism and Hospitality' => ['uz' => 'Turizm va mehmondo‘stlik', 'ru' => 'Туризм и гостиничное дело', 'ar' => 'السياحة والضيافة'],
            'Design: footwear and accessories design' => ['uz' => 'Dizayn: poyabzal va aksessuarlar dizayni', 'ru' => 'Дизайн: дизайн обуви и аксессуаров', 'ar' => 'التصميم: تصميم الأحذية والإكسسوارات'],
            'Design: apparel and textile design' => ['uz' => 'Dizayn: kiyim va to‘qimachilik dizayni', 'ru' => 'Дизайн: дизайн одежды и текстиля', 'ar' => 'التصميم: تصميم الملابس والمنسوجات'],
            'Design: textile and light industry design' => ['uz' => 'Dizayn: to‘qimachilik va yengil sanoat dizayni', 'ru' => 'Дизайн: дизайн текстиля и легкой промышленности', 'ar' => 'التصميم: تصميم المنسوجات والصناعات الخفيفة'],
            'Energy Engineering' => ['uz' => 'Energetika muhandisligi', 'ru' => 'Энергетическая инженерия', 'ar' => 'هندسة الطاقة'],
            'Electrical Engineering' => ['uz' => 'Elektr muhandisligi', 'ru' => 'Электротехника', 'ar' => 'الهندسة الكهربائية'],
            'Environmental Engineering' => ['uz' => 'Atrof-muhit muhandisligi', 'ru' => 'Экологическая инженерия', 'ar' => 'الهندسة البيئية'],
            'Renewable Energy Sources' => ['uz' => 'Qayta tiklanuvchi energiya manbalari', 'ru' => 'Возобновляемые источники энергии', 'ar' => 'مصادر الطاقة المتجددة'],
            'Mechanical Engineering' => ['uz' => 'Mashinasozlik', 'ru' => 'Машиностроение', 'ar' => 'الهندسة الميكانيكية'],
            'Technological Machines and Equipment' => ['uz' => 'Texnologik mashinalar va jihozlar', 'ru' => 'Технологические машины и оборудование', 'ar' => 'الآلات والمعدات التكنولوجية'],
            'Light Industry Engineering' => ['uz' => 'Yengil sanoat muhandisligi', 'ru' => 'Инженерия легкой промышленности', 'ar' => 'هندسة الصناعات الخفيفة'],
            'Industrial Engineering' => ['uz' => 'Sanoat muhandisligi', 'ru' => 'Промышленная инженерия', 'ar' => 'الهندسة الصناعية'],
            'Architecture' => ['uz' => 'Arxitektura', 'ru' => 'Архитектура', 'ar' => 'العمارة'],
            'Civil Engineering' => ['uz' => 'Qurilish muhandisligi', 'ru' => 'Гражданское строительство', 'ar' => 'الهندسة المدنية'],
            'Construction and Operation of Engineering Communications' => ['uz' => 'Muhandislik kommunikatsiyalarini qurish va ekspluatatsiya qilish', 'ru' => 'Строительство и эксплуатация инженерных коммуникаций', 'ar' => 'إنشاء وتشغيل الشبكات الهندسية'],
            'Highway Engineering' => ['uz' => 'Avtomobil yo‘llari muhandisligi', 'ru' => 'Дорожная инженерия', 'ar' => 'هندسة الطرق'],
            'Reconstruction and Restoration of Architectural Monuments' => ['uz' => 'Me’moriy yodgorliklarni rekonstruksiya va restavratsiya qilish', 'ru' => 'Реконструкция и реставрация архитектурных памятников', 'ar' => 'إعادة بناء وترميم المعالم المعمارية'],
            'Urban Planning and Design' => ['uz' => 'Shaharsozlik va dizayn', 'ru' => 'Градостроительство и дизайн', 'ar' => 'التخطيط والتصميم العمراني'],
            'Production of Construction Materials, Products and Structures' => ['uz' => 'Qurilish materiallari, buyumlari va konstruksiyalarini ishlab chiqarish', 'ru' => 'Производство строительных материалов, изделий и конструкций', 'ar' => 'إنتاج مواد ومنتجات وهياكل البناء'],
            'Ecology and Environmental Protection' => ['uz' => 'Ekologiya va atrof-muhit muhofazasi', 'ru' => 'Экология и охрана окружающей среды', 'ar' => 'البيئة وحماية البيئة'],
            'Chemical Engineering' => ['uz' => 'Kimyoviy muhandislik', 'ru' => 'Химическая инженерия', 'ar' => 'الهندسة الكيميائية'],
            'Biotechnology' => ['uz' => 'Biotexnologiya', 'ru' => 'Биотехнология', 'ar' => 'التكنولوجيا الحيوية'],
            'Food Technology' => ['uz' => 'Oziq-ovqat texnologiyasi', 'ru' => 'Пищевая технология', 'ar' => 'تكنولوجيا الأغذية'],
            'Perfumery and Cosmetics Technology' => ['uz' => 'Parfyumeriya va kosmetika texnologiyasi', 'ru' => 'Технология парфюмерии и косметики', 'ar' => 'تكنولوجيا العطور ومستحضرات التجميل'],
            'Deep Gas Processing Technology' => ['uz' => 'Gazni chuqur qayta ishlash texnologiyasi', 'ru' => 'Технология глубокой переработки газа', 'ar' => 'تكنولوجيا المعالجة العميقة للغاز'],
            'Oil and Oil-Gas Refining Technology' => ['uz' => 'Neft va neft-gazni qayta ishlash texnologiyasi', 'ru' => 'Технология переработки нефти и нефтегаза', 'ar' => 'تكنولوجيا تكرير النفط والنفط والغاز'],
            'Geology, Mineral Prospecting and Exploration' => ['uz' => 'Geologiya, foydali qazilmalarni qidirish va razvedka qilish', 'ru' => 'Геология, поиск и разведка полезных ископаемых', 'ar' => 'الجيولوجيا والتنقيب واستكشاف المعادن'],
            'Oil and Gas Business' => ['uz' => 'Neft va gaz ishi', 'ru' => 'Нефтегазовое дело', 'ar' => 'أعمال النفط والغاز'],
            'Technology of Storage and Processing of Agricultural Products' => ['uz' => 'Qishloq xo‘jaligi mahsulotlarini saqlash va qayta ishlash texnologiyasi', 'ru' => 'Технология хранения и переработки сельскохозяйственной продукции', 'ar' => 'تكنولوجيا تخزين ومعالجة المنتجات الزراعية'],
            'Animal Husbandry Engineering' => ['uz' => 'Chorvachilik muhandisligi', 'ru' => 'Инженерия животноводства', 'ar' => 'هندسة تربية الحيوان'],
            'Fruit-Vegetable Growing and Viticulture' => ['uz' => 'Meva-sabzavotchilik va uzumchilik', 'ru' => 'Плодоовощеводство и виноградарство', 'ar' => 'زراعة الفواكه والخضروات والكروم'],
            'Cosmetology' => ['uz' => 'Kosmetologiya', 'ru' => 'Косметология', 'ar' => 'التجميل'],
            'Occupational Health and Safety' => ['uz' => 'Mehnat muhofazasi va texnika xavfsizligi', 'ru' => 'Охрана труда и техника безопасности', 'ar' => 'الصحة والسلامة المهنية'],
            'Hydrology' => ['uz' => 'Gidrologiya', 'ru' => 'Гидрология', 'ar' => 'الهيدرولوجيا'],
            'Hydropower Engineering' => ['uz' => 'Gidroenergetika muhandisligi', 'ru' => 'Гидроэнергетическая инженерия', 'ar' => 'هندسة الطاقة الكهرومائية'],
            'Vehicle Engineering' => ['uz' => 'Transport vositalari muhandisligi', 'ru' => 'Транспортное машиностроение', 'ar' => 'هندسة المركبات'],
            'Geodesy and Geoinformatics' => ['uz' => 'Geodeziya va geoinformatika', 'ru' => 'Геодезия и геоинформатика', 'ar' => 'الجيوديسيا والمعلوماتية الجغرافية'],
            'Cartography and Remote Sensing' => ['uz' => 'Kartografiya va masofadan zondlash', 'ru' => 'Картография и дистанционное зондирование', 'ar' => 'رسم الخرائط والاستشعار عن بعد'],
            'Cadastre' => ['uz' => 'Kadastr', 'ru' => 'Кадастр', 'ar' => 'السجل العقاري'],
            'Hydrotechnical and Geotechnical Engineering' => ['uz' => 'Gidrotexnika va geotexnika muhandisligi', 'ru' => 'Гидротехническая и геотехническая инженерия', 'ar' => 'الهندسة الهيدروليكية والجيوتقنية'],
            'Mechanization of Agriculture' => ['uz' => 'Qishloq xo‘jaligini mexanizatsiyalash', 'ru' => 'Механизация сельского хозяйства', 'ar' => 'ميكنة الزراعة'],
            'Water Management and Melioration' => ['uz' => 'Suv xo‘jaligi va melioratsiya', 'ru' => 'Водное хозяйство и мелиорация', 'ar' => 'إدارة المياه واستصلاح الأراضي'],
            'Operation of Hydrotechnical Installations and Pumping Stations' => ['uz' => 'Gidrotexnika inshootlari va nasos stansiyalaridan foydalanish', 'ru' => 'Эксплуатация гидротехнических сооружений и насосных станций', 'ar' => 'تشغيل المنشآت الهيدروليكية ومحطات الضخ'],
            'Meliorative Hydrogeology' => ['uz' => 'Meliorativ gidrogeologiya', 'ru' => 'Мелиоративная гидрогеология', 'ar' => 'الهيدروجيولوجيا الاستصلاحية'],
            'Water Supply Engineering Systems' => ['uz' => 'Suv ta’minoti muhandislik tizimlari', 'ru' => 'Инженерные системы водоснабжения', 'ar' => 'أنظمة هندسة إمدادات المياه'],
            'Land Cadastre and Land Management' => ['uz' => 'Yer kadastri va yer tuzish', 'ru' => 'Земельный кадастр и землеустройство', 'ar' => 'السجل العقاري وإدارة الأراضي'],
        ];
    }

    private function phraseMap(): array
    {
        return [
            'uz' => [
                'Faculty of' => 'fakulteti', 'Department of' => 'kafedrasi', 'Department' => 'kafedrasi', 'Engineering' => 'muhandisligi',
                'Technology' => 'texnologiyasi', 'Technologies' => 'texnologiyalari', 'Management' => 'boshqaruv', 'Service' => 'servis',
                'Digitalization' => 'raqamlashtirish', 'Natural Resources' => 'tabiiy resurslar', 'Economics' => 'iqtisodiyot',
                'and' => 'va', 'Daily' => 'Har kuni', 'Monday' => 'Dushanba', 'Friday' => 'Juma', 'Saturday' => 'Shanba',
                'Academic Affairs' => 'o‘quv ishlari', 'Youth Affairs' => 'yoshlar masalalari',
            ],
            'ru' => [
                'Faculty of' => 'Факультет', 'Department of' => 'Кафедра', 'Department' => 'Кафедра', 'Engineering' => 'инженерия',
                'Technology' => 'технология', 'Technologies' => 'технологии', 'Management' => 'управление', 'Service' => 'сервис',
                'Digitalization' => 'цифровизация', 'Natural Resources' => 'природные ресурсы', 'Economics' => 'экономика',
                'and' => 'и', 'Daily' => 'Ежедневно', 'Monday' => 'Понедельник', 'Friday' => 'Пятница', 'Saturday' => 'Суббота',
                'Academic Affairs' => 'учебная работа', 'Youth Affairs' => 'дела молодежи',
            ],
            'ar' => [
                'Faculty of' => 'كلية', 'Department of' => 'قسم', 'Department' => 'قسم', 'Engineering' => 'الهندسة',
                'Technology' => 'التكنولوجيا', 'Technologies' => 'التقنيات', 'Management' => 'الإدارة', 'Service' => 'الخدمات',
                'Digitalization' => 'الرقمنة', 'Natural Resources' => 'الموارد الطبيعية', 'Economics' => 'الاقتصاد',
                'except' => 'ما عدا', 'and' => 'و', 'Daily' => 'يومياً', 'Monday' => 'الاثنين', 'Friday' => 'الجمعة', 'Saturday' => 'السبت',
                'Office hours:' => 'ساعات الاستقبال:', 'Phone:' => 'الهاتف:', 'Email:' => 'البريد الإلكتروني:',
                "Bachelor's Degree" => 'درجة البكالوريوس', "Master's Degree" => 'درجة الماجستير',
                'Academic Affairs' => 'الشؤون الأكاديمية', 'Youth Affairs' => 'شؤون الشباب',
            ],
        ];
    }

    private function nameMap(): array
    {
        return [
            'Sadoqat Gafforovna Siddiqova' => 'سدوكات غافوروفنا صديقوفا',
            'Marhabo Davlatovna Pardayeva' => 'مرحبـو دولتوفنا برداييفا',
            'Aslitdin Badreddinovich Nizamov' => 'أصل الدين بدر الدينوفيتش نظاموف',
            'Alisher Xolmurodovich Gafforov' => 'عليشر خولمرودوفيتش غفوروف',
            'Sobir Bahronovich Saidov' => 'صابر بهرونوفيتش سعيدوف',
            'Murodjon Ulugbekovich Djurayev' => 'مرادجون ألوغبيكوفيتش جوراييف',
        ];
    }
};

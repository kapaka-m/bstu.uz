<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private array $locales = ['en', 'uz', 'ru', 'ar'];

    public function up(): void
    {
        if (! Schema::hasTable('faculties') || ! Schema::hasTable('department_translations')) {
            return;
        }

        $faculty = DB::table('faculties')->where('slug', 'faculty-of-engineering')->first();
        if (! $faculty) {
            return;
        }

        $departments = DB::table('departments')
            ->where('faculty_id', $faculty->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        foreach ($departments as $department) {
            foreach ($this->locales as $locale) {
                $name = $this->departmentName((int) $department->id, $department->slug, $locale);
                $facultyName = $this->facultyName((int) $faculty->id, $locale);
                $headName = $this->localizedHeadName($department, $locale);
                $programs = $this->programItems((int) $department->id, $locale);
                $description = $this->departmentDescription($name, $facultyName, $locale);
                $sections = $this->sections($department, $name, $facultyName, $headName, $programs, $locale);

                DB::table('department_translations')->updateOrInsert(
                    ['department_id' => $department->id, 'locale' => $locale],
                    [
                        'description' => $description,
                        'content_sections' => json_encode($sections, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                        'meta_description' => Str::limit($description, 240, ''),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }

        $this->fixEngineeringStaffProfileNames();

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        // Intentionally not reversible: this normalizes localized public academic content.
    }

    private function departmentName(int $id, string $slug, string $locale): string
    {
        return DB::table('department_translations')
            ->where('department_id', $id)
            ->where('locale', $locale)
            ->value('name')
            ?: $this->fallbackDepartmentNames()[$slug][$locale]
            ?? Str::headline($slug);
    }

    private function facultyName(int $id, string $locale): string
    {
        return DB::table('faculty_translations')
            ->where('faculty_id', $id)
            ->where('locale', $locale)
            ->value('name')
            ?: match ($locale) {
                'uz' => 'Muhandislik fakulteti',
                'ru' => 'Инженерный факультет',
                'ar' => 'كلية الهندسة',
                default => 'Faculty of Engineering',
            };
    }

    private function localizedHeadName(object $department, string $locale): string
    {
        $profile = DB::table('staff_profiles')
            ->where('department_id', $department->id)
            ->where(function ($query) use ($department) {
                $query->where('email', $department->email);
                if ($department->head_name) {
                    $query->orWhereIn('id', function ($subQuery) use ($department) {
                        $subQuery->select('staff_profile_id')
                            ->from('staff_profile_translations')
                            ->where('locale', 'en')
                            ->where('full_name', $department->head_name);
                    });
                }
            })
            ->orderBy('sort_order')
            ->first();

        if (! $profile) {
            $profile = DB::table('staff_profiles')
                ->where('department_id', $department->id)
                ->orderBy('sort_order')
                ->first();
        }

        if (! $profile) {
            return (string) $department->head_name;
        }

        return DB::table('staff_profile_translations')
            ->where('staff_profile_id', $profile->id)
            ->where('locale', $locale)
            ->value('full_name')
            ?: (string) $department->head_name;
    }

    private function programItems(int $departmentId, string $locale): array
    {
        $programs = DB::table('programs')
            ->join('program_translations', 'programs.id', '=', 'program_translations.program_id')
            ->where('programs.department_id', $departmentId)
            ->where('programs.is_active', true)
            ->where('program_translations.locale', $locale)
            ->orderBy('programs.sort_order')
            ->get(['programs.official_code', 'program_translations.name']);

        return $programs
            ->map(fn ($program) => trim(($program->official_code ? $program->official_code.' - ' : '').$program->name))
            ->filter()
            ->values()
            ->all();
    }

    private function departmentDescription(string $name, string $facultyName, string $locale): string
    {
        $cleanName = $this->nameForSentence($name, $locale);
        $cleanFacultyName = $this->facultyForSentence($facultyName, $locale);

        return match ($locale) {
            'uz' => "{$name} {$facultyName} tarkibida muhandislik ta'limi, laboratoriya mashg'ulotlari, amaliy loyihalar va ishlab chiqarish bilan hamkorlikni birlashtiradi.",
            'ru' => "Кафедра {$cleanName} в составе {$cleanFacultyName} объединяет инженерное образование, лабораторную подготовку, практические проекты и сотрудничество с производственными организациями.",
            'ar' => "{$name} ضمن {$facultyName} يجمع بين التعليم الهندسي والتدريب المخبري والمشروعات العملية والتعاون مع المؤسسات الإنتاجية.",
            default => "{$name} is part of {$facultyName} and combines engineering education, laboratory training, practical projects, and cooperation with industry partners.",
        };
    }

    private function sections(object $department, string $name, string $facultyName, string $headName, array $programs, string $locale): array
    {
        $programItems = $programs ?: [$this->noProgramsText($locale)];
        $office = $this->schedule((string) $department->reception_time, $locale);
        $email = $department->email ?: '-';
        $phone = $department->phone ?: '-';

        return [
            [
                'key' => 'overview',
                'title' => $this->label('overview', $locale),
                'items' => [$this->departmentDescription($name, $facultyName, $locale)],
            ],
            [
                'key' => 'subjects',
                'title' => $this->label('subjects', $locale),
                'items' => [$this->subjectsText($name, $locale)],
            ],
            [
                'key' => 'prepared_specialists',
                'title' => $this->label('programs', $locale),
                'items' => $programItems,
            ],
            [
                'key' => 'research',
                'title' => $this->label('research', $locale),
                'items' => [$this->researchText($name, $locale)],
            ],
            [
                'key' => 'cooperation',
                'title' => $this->label('cooperation', $locale),
                'items' => [$this->cooperationText($name, $locale)],
            ],
            [
                'key' => 'department_structure',
                'title' => $this->label('structure', $locale),
                'items' => [
                    $this->label('head', $locale).': '.$headName,
                    $this->label('office', $locale).': '.$office,
                    $this->label('phone', $locale).': '.$phone,
                    $this->label('email', $locale).': '.$email,
                ],
            ],
        ];
    }

    private function subjectsText(string $name, string $locale): string
    {
        $cleanName = $this->nameForSentence($name, $locale);

        return match ($locale) {
            'uz' => "{$name} bo'yicha fanlar tayanch nazariy bilimlar, kasbiy modullar, laboratoriya ishlari, loyiha mashg'ulotlari va ishlab chiqarish amaliyotini qamrab oladi.",
            'ru' => "Учебные дисциплины кафедры {$cleanName} включают базовую теоретическую подготовку, профильные модули, лабораторные работы, проектные занятия и производственную практику.",
            'ar' => "تشمل المواد الدراسية في {$name} المعارف النظرية الأساسية والوحدات التخصصية والعمل المخبري ومشروعات التصميم والتدريب الصناعي.",
            default => "Courses taught by {$name} cover core theoretical knowledge, professional modules, laboratory work, design projects, and industrial practice.",
        };
    }

    private function researchText(string $name, string $locale): string
    {
        $cleanName = $this->nameForSentence($name, $locale);

        return match ($locale) {
            'uz' => "{$name} professor-o'qituvchilari amaliy muhandislik masalalari, innovatsion texnologiyalar, energiya samaradorligi, materiallar, loyihalash va ishlab chiqarish jarayonlari bo'yicha ilmiy ishlar olib boradi.",
            'ru' => "Преподаватели кафедры {$cleanName} проводят научные исследования по прикладным инженерным задачам, инновационным технологиям, энергоэффективности, материалам, проектированию и производственным процессам.",
            'ar' => "ينفذ أعضاء هيئة التدريس في {$name} أبحاثاً في القضايا الهندسية التطبيقية والتقنيات المبتكرة وكفاءة الطاقة والمواد والتصميم وعمليات الإنتاج.",
            default => "Faculty members of {$name} conduct research on applied engineering problems, innovative technologies, energy efficiency, materials, design, and production processes.",
        };
    }

    private function cooperationText(string $name, string $locale): string
    {
        return match ($locale) {
            'uz' => "{$name} ta'lim jarayonini ishlab chiqarish korxonalari, ilmiy tashkilotlar va xalqaro hamkorlar bilan hamkorlikda rivojlantiradi.",
            'ru' => "{$name} развивает образовательный процесс во взаимодействии с производственными предприятиями, научными организациями и международными партнерами.",
            'ar' => "يطور {$name} العملية التعليمية بالتعاون مع المؤسسات الإنتاجية والمنظمات البحثية والشركاء الدوليين.",
            default => "{$name} develops the educational process in cooperation with industrial enterprises, research organizations, and international partners.",
        };
    }

    private function noProgramsText(string $locale): string
    {
        return match ($locale) {
            'uz' => "Dasturlar universitet qabul rejasiga muvofiq yangilanadi.",
            'ru' => "Программы обновляются в соответствии с планом приема университета.",
            'ar' => "يتم تحديث البرامج وفق خطة القبول الجامعية.",
            default => "Programs are updated according to the university admission plan.",
        };
    }

    private function schedule(string $value, string $locale): string
    {
        $value = trim($value);
        $timePattern = '([0-2]?\d:[0-5]\d\s*[-–]\s*[0-2]?\d:[0-5]\d)';

        if (preg_match('/monday\s*[-–]\s*friday\s+'.$timePattern.'/i', $value, $m)) {
            return match ($locale) {
                'uz' => 'Dushanba-juma '.$m[1],
                'ru' => 'Понедельник-пятница '.$m[1],
                'ar' => 'الاثنين-الجمعة '.$m[1],
                default => $value,
            };
        }

        if (preg_match('/tuesday\s*[-–]\s*thursday\s+'.$timePattern.'/i', $value, $m)) {
            return match ($locale) {
                'uz' => 'Seshanba-payshanba '.$m[1],
                'ru' => 'Вторник-четверг '.$m[1],
                'ar' => 'الثلاثاء-الخميس '.$m[1],
                default => $value,
            };
        }

        if (preg_match('/daily\s+'.$timePattern.'/i', $value, $m)) {
            return match ($locale) {
                'uz' => 'Har kuni '.$m[1],
                'ru' => 'Ежедневно '.$m[1],
                'ar' => 'يومياً '.$m[1],
                default => $value,
            };
        }

        return $value ?: '-';
    }

    private function nameForSentence(string $name, string $locale): string
    {
        if ($locale === 'ru') {
            return trim(preg_replace('/^Кафедра\s+/u', '', $name) ?? $name);
        }

        return $name;
    }

    private function facultyForSentence(string $name, string $locale): string
    {
        if ($locale === 'ru' && $name === 'Инженерный факультет') {
            return 'Инженерного факультета';
        }

        return $name;
    }

    private function label(string $key, string $locale): string
    {
        $labels = [
            'overview' => ['en' => 'About the Department', 'uz' => 'Kafedra haqida', 'ru' => 'О кафедре', 'ar' => 'عن القسم'],
            'subjects' => ['en' => 'Taught Subjects', 'uz' => "O'qitiladigan fanlar", 'ru' => 'Учебные дисциплины', 'ar' => 'المواد التي تدرس في القسم'],
            'programs' => ['en' => 'Programs and Specializations', 'uz' => 'Dasturlar va mutaxassisliklar', 'ru' => 'Программы и специализации', 'ar' => 'البرامج والتخصصات'],
            'research' => ['en' => 'Research Work', 'uz' => 'Ilmiy ishlar', 'ru' => 'Научная работа', 'ar' => 'الأعمال البحثية'],
            'cooperation' => ['en' => 'International Cooperation', 'uz' => 'Xalqaro hamkorlik', 'ru' => 'Международное сотрудничество', 'ar' => 'التعاون الدولي'],
            'structure' => ['en' => 'Department Structure', 'uz' => 'Kafedra tuzilmasi', 'ru' => 'Структура кафедры', 'ar' => 'هيكل القسم'],
            'head' => ['en' => 'Head of Department', 'uz' => 'Kafedra mudiri', 'ru' => 'Заведующий кафедрой', 'ar' => 'رئيس القسم'],
            'office' => ['en' => 'Office hours', 'uz' => 'Qabul vaqti', 'ru' => 'Часы приема', 'ar' => 'ساعات الاستقبال'],
            'phone' => ['en' => 'Phone', 'uz' => 'Telefon', 'ru' => 'Телефон', 'ar' => 'الهاتف'],
            'email' => ['en' => 'Email', 'uz' => 'Elektron pochta', 'ru' => 'Электронная почта', 'ar' => 'البريد الإلكتروني'],
        ];

        return $labels[$key][$locale] ?? $labels[$key]['en'];
    }

    private function fixEngineeringStaffProfileNames(): void
    {
        if (! Schema::hasTable('staff_profiles') || ! Schema::hasTable('staff_profile_translations')) {
            return;
        }

        $profileId = DB::table('staff_profiles')
            ->where('slug', 'architecture-of-the-technical-sciences-roziyev-hoshim-roziyevich')
            ->value('id');

        if (! $profileId) {
            return;
        }

        $names = [
            'en' => "Roziyev Hoshim Ro'ziyevich",
            'uz' => "Roziyev Hoshim Ro'ziyevich",
            'ru' => 'Розиев Хошим Рузиевич',
            'ar' => 'روزييف هوشيم روزييفيتش',
        ];

        $positions = [
            'en' => 'Candidate of Technical Sciences',
            'uz' => 'Texnika fanlari nomzodi',
            'ru' => 'Кандидат технических наук',
            'ar' => 'مرشح في العلوم التقنية',
        ];

        $departmentNames = [
            'en' => 'Architecture',
            'uz' => 'Arxitektura kafedrasi',
            'ru' => 'Кафедра архитектуры',
            'ar' => 'قسم العمارة',
        ];

        foreach ($this->locales as $locale) {
            $name = $names[$locale];
            $position = $positions[$locale];
            $department = $departmentNames[$locale];
            $bio = match ($locale) {
                'uz' => "{$name} {$department} tarkibida {$position} sifatida faoliyat yuritadi. U o'quv, ilmiy-uslubiy va tashkiliy jarayonlarni rivojlantirishga hissa qo'shadi.",
                'ru' => "{$name} работает в подразделении «{$department}» в должности «{$position}». Сотрудник участвует в развитии учебной, научно-методической и организационной деятельности.",
                'ar' => "{$name} يعمل/تعمل في {$department} بصفة {$position}. ويساهم/تساهم في تطوير العملية التعليمية والمنهجية والتنظيمية.",
                default => "{$name} serves as {$position} in {$department}, contributing to academic, methodological, and organizational development.",
            };

            DB::table('staff_profile_translations')->updateOrInsert(
                ['staff_profile_id' => $profileId, 'locale' => $locale],
                [
                    'full_name' => $name,
                    'position' => $position,
                    'bio' => $bio,
                    'office' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    private function fallbackDepartmentNames(): array
    {
        return [
            'electrical-power-engineering' => ['en' => 'Electrical and Power Engineering', 'uz' => 'Elektr va energetika muhandisligi kafedrasi', 'ru' => 'Кафедра электроэнергетики', 'ar' => 'قسم الهندسة الكهربائية والطاقة'],
            'architecture' => ['en' => 'Architecture', 'uz' => 'Arxitektura kafedrasi', 'ru' => 'Кафедра архитектуры', 'ar' => 'قسم العمارة'],
            'civil-engineering' => ['en' => 'Civil Engineering', 'uz' => 'Qurilish muhandisligi kafedrasi', 'ru' => 'Кафедра гражданского строительства', 'ar' => 'قسم الهندسة المدنية'],
            'light-industry-engineering-and-design' => ['en' => 'Light Industry Engineering and Design', 'uz' => 'Yengil sanoat muhandisligi va dizayni kafedrasi', 'ru' => 'Кафедра инженерии и дизайна легкой промышленности', 'ar' => 'قسم هندسة وتصميم الصناعات الخفيفة'],
            'mechanics-engineering-graphics' => ['en' => 'Mechanics and Engineering Graphics', 'uz' => 'Mexanika va muhandislik grafikasi kafedrasi', 'ru' => 'Кафедра механики и инженерной графики', 'ar' => 'قسم الميكانيكا والرسم الهندسي'],
            'technological-machines-equipment' => ['en' => 'Technological Machines and Equipment', 'uz' => 'Texnologik mashinalar va jihozlar kafedrasi', 'ru' => 'Кафедра технологических машин и оборудования', 'ar' => 'قسم الآلات والمعدات التكنولوجية'],
            'textile-materials-science' => ['en' => 'Textile Materials Science', 'uz' => "To'qimachilik materialshunosligi kafedrasi", 'ru' => 'Кафедра текстильного материаловедения', 'ar' => 'قسم علم مواد النسيج'],
        ];
    }
};

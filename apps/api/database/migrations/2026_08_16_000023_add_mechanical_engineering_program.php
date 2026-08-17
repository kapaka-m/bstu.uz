<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $department = DB::table('departments')
            ->where('slug', 'mechanics-engineering-graphics')
            ->first(['id', 'faculty_id']);

        if (! $department) {
            return;
        }

        $facultyId = $department->faculty_id
            ?: DB::table('faculties')->where('slug', 'faculty-of-engineering')->value('id');

        if (! $facultyId) {
            return;
        }

        DB::transaction(function () use ($department, $facultyId) {
            $programId = DB::table('programs')
                ->where('slug', 'mechanical-engineering-60712300')
                ->orWhere('official_code', '60712300')
                ->orWhere('code', '60712300')
                ->value('id');

            $programPayload = [
                'faculty_id' => $facultyId,
                'department_id' => $department->id,
                'slug' => 'mechanical-engineering-60712300',
                'code' => '60712300',
                'official_code' => '60712300',
                'track' => null,
                'degree' => 'bachelor',
                'duration_years' => 4,
                'study_mode' => 'full_time',
                'language_of_study' => 'uzbek',
                'tuition_fee' => 0,
                'currency' => 'UZS',
                'image' => 'programs/mechanical-engineering-60712300.jpg',
                'is_active' => true,
                'show_on_homepage' => false,
                'homepage_sort_order' => 0,
                'sort_order' => 50,
                'updated_at' => now(),
            ];

            if ($programId) {
                DB::table('programs')->where('id', $programId)->update($programPayload);
            } else {
                $programPayload['created_at'] = now();
                $programId = DB::table('programs')->insertGetId($programPayload);
            }

            foreach ($this->translations() as $locale => $translation) {
                DB::table('program_translations')->updateOrInsert(
                    ['program_id' => $programId, 'locale' => $locale],
                    [
                        'name' => $translation['name'],
                        'description' => $translation['description'],
                        'requirements' => $translation['requirements'],
                        'documents' => $translation['documents'],
                        'curriculum_summary' => $translation['curriculum_summary'],
                        'career_opportunities' => $translation['career_opportunities'],
                        'meta_title' => $translation['meta_title'],
                        'meta_description' => $translation['meta_description'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function translations(): array
    {
        return [
            'en' => [
                'name' => 'Mechanical Engineering',
                'description' => 'Mechanical Engineering is a four-year bachelor program in the Department of Mechanics and Engineering Graphics. It combines mechanics, engineering graphics, CAD, machine elements, laboratory work, design projects, and industrial practice.',
                'requirements' => "Secondary education certificate\nPassport or identity document\nApplication according to admission requirements",
                'documents' => "Passport copy\nEducation document\nPhoto",
                'curriculum_summary' => "Theoretical mechanics\nApplied mechanics\nEngineering graphics\nComputer-aided design\nMachine elements\nIndustrial practice\nGraduation project",
                'career_opportunities' => "Mechanical engineer\nDesign engineer\nCAD specialist\nProduction technologist\nMaintenance engineer\nQuality control specialist",
                'meta_title' => '60712300 - Mechanical Engineering',
                'meta_description' => 'Mechanical Engineering is a four-year bachelor program focused on mechanics, engineering graphics, CAD, machine elements, and industrial practice.',
            ],
            'uz' => [
                'name' => 'Mexanika muhandisligi',
                'description' => 'Mexanika muhandisligi Mexanika va muhandislik grafikasi kafedrasidagi to‘rt yillik bakalavriat dasturidir. Dastur mexanika, muhandislik grafikasi, CAD, mashina detallari, laboratoriya ishlari, loyiha ishlari va ishlab chiqarish amaliyotini birlashtiradi.',
                'requirements' => "O‘rta taʼlim hujjati\nPasport yoki shaxsni tasdiqlovchi hujjat\nQabul talablariga muvofiq ariza",
                'documents' => "Pasport nusxasi\nTaʼlim hujjati\nFotosurat",
                'curriculum_summary' => "Nazariy mexanika\nAmaliy mexanika\nMuhandislik grafikasi\nKompyuter yordamida loyihalash\nMashina detallari\nIshlab chiqarish amaliyoti\nBitiruv loyihasi",
                'career_opportunities' => "Mexanik muhandis\nLoyiha muhandisi\nCAD mutaxassisi\nIshlab chiqarish texnologi\nTexnik xizmat ko‘rsatish muhandisi\nSifat nazorati mutaxassisi",
                'meta_title' => '60712300 - Mexanika muhandisligi',
                'meta_description' => 'Mexanika muhandisligi mexanika, muhandislik grafikasi, CAD, mashina detallari va ishlab chiqarish amaliyotiga yo‘naltirilgan to‘rt yillik bakalavriat dasturidir.',
            ],
            'ru' => [
                'name' => 'Машиностроение',
                'description' => 'Машиностроение — четырехлетняя бакалаврская программа кафедры механики и инженерной графики. Программа объединяет механику, инженерную графику, CAD, детали машин, лабораторные занятия, проектные работы и производственную практику.',
                'requirements' => "Документ о среднем образовании\nПаспорт или удостоверение личности\nЗаявление согласно правилам приема",
                'documents' => "Копия паспорта\nДокумент об образовании\nФотография",
                'curriculum_summary' => "Теоретическая механика\nПрикладная механика\nИнженерная графика\nКомпьютерное проектирование\nДетали машин\nПроизводственная практика\nВыпускной проект",
                'career_opportunities' => "Инженер-механик\nИнженер-конструктор\nCAD-специалист\nТехнолог производства\nИнженер по техническому обслуживанию\nСпециалист по контролю качества",
                'meta_title' => '60712300 - Машиностроение',
                'meta_description' => 'Машиностроение — четырехлетняя бакалаврская программа по механике, инженерной графике, CAD, деталям машин и производственной практике.',
            ],
            'ar' => [
                'name' => 'الهندسة الميكانيكية',
                'description' => 'الهندسة الميكانيكية برنامج بكالوريوس مدته أربع سنوات في قسم الميكانيكا والرسم الهندسي. يجمع البرنامج بين الميكانيكا، والرسم الهندسي، والتصميم بمساعدة الحاسوب، وعناصر الآلات، والعمل المخبري، ومشروعات التصميم، والتدريب الصناعي.',
                'requirements' => "شهادة التعليم الثانوي\nجواز سفر أو وثيقة هوية\nطلب وفق متطلبات القبول",
                'documents' => "نسخة من جواز السفر\nوثيقة التعليم\nصورة شخصية",
                'curriculum_summary' => "الميكانيكا النظرية\nالميكانيكا التطبيقية\nالرسم الهندسي\nالتصميم بمساعدة الحاسوب\nعناصر الآلات\nالتدريب الصناعي\nمشروع التخرج",
                'career_opportunities' => "مهندس ميكانيكي\nمهندس تصميم\nمتخصص CAD\nتقني إنتاج\nمهندس صيانة\nمتخصص مراقبة الجودة",
                'meta_title' => '60712300 - الهندسة الميكانيكية',
                'meta_description' => 'الهندسة الميكانيكية برنامج بكالوريوس مدته أربع سنوات يركز على الميكانيكا والرسم الهندسي والتصميم بمساعدة الحاسوب وعناصر الآلات والتدريب الصناعي.',
            ],
        ];
    }
};

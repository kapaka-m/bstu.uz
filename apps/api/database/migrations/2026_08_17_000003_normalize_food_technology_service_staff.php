<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $departmentId = DB::table('departments')->where('slug', 'food-technology-service')->value('id');

        if (! $departmentId) {
            return;
        }

        DB::transaction(function () use ($departmentId) {
            $this->deleteBrokenStaff($departmentId);
            $this->renameLegacySlugs($departmentId);
            $this->normalizeStaff($departmentId);
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
                'food-technology-service-candidate-of-the-technical-sciences',
                'food-technology-service-kata-teacher',
                'food-technology-service-head-of-the-cabinet-khamroyev',
            ])
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        DB::table('staff_profile_translations')->whereIn('staff_profile_id', $ids)->delete();
        DB::table('staff_profiles')->whereIn('id', $ids)->delete();
    }

    private function renameLegacySlugs(int $departmentId): void
    {
        $oldSlug = 'food-technology-service-jonibek-sadilloyevich';
        $newSlug = 'food-technology-service-khamroyev-jonibek-sadilloyevich';

        $oldId = DB::table('staff_profiles')->where('department_id', $departmentId)->where('slug', $oldSlug)->value('id');

        if (! $oldId) {
            return;
        }

        $existingId = DB::table('staff_profiles')->where('department_id', $departmentId)->where('slug', $newSlug)->value('id');

        if ($existingId && (int) $existingId !== (int) $oldId) {
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
                        'bio' => $translation['name'].' serves as '.$translation['position'].' in Food Technology and Service, contributing to teaching, laboratory work, and academic development.',
                        'office' => 'Monday-Saturday 14:00-16:00',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }
        }
    }

    private function staff(): array
    {
        return [
            $this->staffMember('food-technology-service-qurbonov-murod-tashpulatovich', 'Qurbonov Murod Tashpulatovich', 'Head of Department', 'قربونوف مراد تاشبولاتوفيتش', 'رئيس القسم', 'Курбонов Мурод Ташпулатович', 'Заведующий кафедрой'),
            $this->staffMember('food-technology-service-khaydarzoda-lolitta-negmatovna', 'Khaydarzoda Lolitta Negmatovna', 'Candidate of Technical Sciences, Associate Professor', 'خيدرزاده لوليتا نيغماتوفنا', 'مرشحة في العلوم التقنية، أستاذة مشاركة', 'Хайдарзода Лолита Негматовна', 'Кандидат технических наук, доцент'),
            $this->staffMember('food-technology-service-atamuradova-tamara-ivanovna', 'Atamuradova Tamara Ivanovna', 'Candidate of Technical Sciences, Associate Professor', 'أتامورادوفا تمارا إيفانوفنا', 'مرشحة في العلوم التقنية، أستاذة مشاركة', 'Атамурадова Тамара Ивановна', 'Кандидат технических наук, доцент'),
            $this->staffMember('food-technology-service-ergasheva-khusrabo-bobonazarovna', 'Ergasheva Khusrabo Bobonazarovna', 'Candidate of Technical Sciences, Associate Professor', 'إرغاشيفا خسربو بوبونازاروفنا', 'مرشحة في العلوم التقنية، أستاذة مشاركة', 'Эргашева Хусрабо Бобоназаровна', 'Кандидат технических наук, доцент'),
            $this->staffMember('food-technology-service-khuzhakulova-nilufar-fayzullayevna', 'Khuzhakulova Nilufar Fayzullayevna', 'Candidate of Technical Sciences, Associate Professor', 'خوجاقولوفا نيلوفر فيض اللهيفنا', 'مرشحة في العلوم التقنية، أستاذة مشاركة', 'Хужакулова Нилуфар Файзуллаевна', 'Кандидат технических наук, доцент'),
            $this->staffMember('food-technology-service-yuldasheva-shabon-jumaevna', 'Yuldasheva Shabon Jumaevna', 'Senior Lecturer', 'يولداشيفا شابون جمعايفنا', 'محاضرة أولى', 'Юлдашева Шабон Жумаевна', 'Старший преподаватель'),
            $this->staffMember('food-technology-service-ismatova-shakhnoza-nusratillonovna', 'Ismatova Shakhnoza Nusratillonovna', 'Candidate of Technical Sciences, Associate Professor', 'إسماتوفا شاهنوزا نصرتيلونوفنا', 'مرشحة في العلوم التقنية، أستاذة مشاركة', 'Исматова Шахноза Нусратиллоновна', 'Кандидат технических наук, доцент'),
            $this->staffMember('food-technology-service-amonov-bobur-nematovich', 'Amonov Bobur Nematovich', 'Assistant', 'أمونوف بوبور نعمتوفيتش', 'مساعد', 'Амонов Бобур Неъматович', 'Ассистент'),
            $this->staffMember('food-technology-service-muzafarova-hall-mukhinovna', 'Muzafarova Hall Mukhinovna', 'Assistant', 'موزافاروفا هال موخينوفنا', 'مساعدة', 'Музафарова Халл Мухиновна', 'Ассистент'),
            $this->staffMember('food-technology-service-khamroyev-jonibek-sadilloyevich', 'Khamroyev Jonibek Sadilloyevich', 'Cabinet Manager', 'خامرويف جونيبيك ساديللوفيتش', 'مدير المكتب', 'Хамроев Жонибек Садиллоевич', 'Заведующий кабинетом'),
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $departmentId = DB::table('departments')->where('slug', 'oil-gas-refining-technology')->value('id');

        if (! $departmentId) {
            return;
        }

        DB::transaction(function () use ($departmentId) {
            $this->deleteBrokenDuplicates($departmentId);
            $this->renameLegacySlugs($departmentId);
            $this->normalizeStaff($departmentId);
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function deleteBrokenDuplicates(int $departmentId): void
    {
        $ids = DB::table('staff_profiles')
            ->where('department_id', $departmentId)
            ->whereIn('slug', [
                'oil-gas-refining-technology-ochilov-abdurasulovich',
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
        foreach ($this->slugMap() as $oldSlug => $newSlug) {
            $oldId = DB::table('staff_profiles')->where('department_id', $departmentId)->where('slug', $oldSlug)->value('id');

            if (! $oldId) {
                continue;
            }

            $existingId = DB::table('staff_profiles')->where('department_id', $departmentId)->where('slug', $newSlug)->value('id');

            if ($existingId && (int) $existingId !== (int) $oldId) {
                DB::table('staff_profile_translations')->where('staff_profile_id', $oldId)->delete();
                DB::table('staff_profiles')->where('id', $oldId)->delete();
                continue;
            }

            DB::table('staff_profiles')->where('id', $oldId)->update([
                'slug' => $newSlug,
                'updated_at' => now(),
            ]);
        }
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
                        'bio' => $translation['name'].' serves as '.$translation['position'].' in Oil and Gas Refining Technology, contributing to teaching, research, and academic development.',
                        'office' => 'Monday-Friday 14:00-16:00',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }
        }
    }

    private function slugMap(): array
    {
        return [
            'oil-gas-refining-technology-0-ochilov-abduraxim-abdurasulovich' => 'oil-gas-refining-technology-ochilov-abduraxim-abdurasulovich',
            'oil-gas-refining-technology-9-maxmudov-muxtor-jamolovich' => 'oil-gas-refining-technology-maxmudov-muxtor-jamolovich',
            'oil-gas-refining-technology-10-hayitov-ruslan-rustamjonovich' => 'oil-gas-refining-technology-hayitov-ruslan-rustamjonovich',
            'oil-gas-refining-technology-11-bozorov-gayrat-rashidovich' => 'oil-gas-refining-technology-bozorov-gayrat-rashidovich',
            'oil-gas-refining-technology-12-xojaqulov-aziz-fayzullaevich' => 'oil-gas-refining-technology-xojaqulov-aziz-fayzullaevich',
            'oil-gas-refining-technology-13-murodov-malik-negmurodovich' => 'oil-gas-refining-technology-murodov-malik-negmurodovich',
            'oil-gas-refining-technology-14-safarov-baxri-djumaevich' => 'oil-gas-refining-technology-safarov-baxri-djumaevich',
            'oil-gas-refining-technology-15-tsukanov-maksim-nikolayevich' => 'oil-gas-refining-technology-tsukanov-maksim-nikolayevich',
        ];
    }

    private function staff(): array
    {
        return [
            $this->staffMember('oil-gas-refining-technology-ochilov-abduraxim-abdurasulovich', 'Ochilov Abduraxim Abdurasulovich', 'Head of Department', 'أوتشيلوف عبد الرحيم عبد الرسول أوفيتش', 'رئيس القسم', 'Очилов Абдурахим Абдурасулович', 'Заведующий кафедрой'),
            $this->staffMember('oil-gas-refining-technology-kadirov-baxtiyor-ganijonovich', 'Kadirov Baxtiyor Ganijonovich', 'Associate Professor, PhD', 'قاديروف بختيار غانيجونوفيتش', 'أستاذ مشارك، دكتوراه', 'Кадиров Бахтиёр Ганижонович', 'Доцент, PhD'),
            $this->staffMember('oil-gas-refining-technology-nazarov-azizbek-bobir-ogli', 'Nazarov Azizbek Bobir o‘g‘li', 'Assistant', 'نزاروف عزيزبيك بوبير أوغلي', 'مساعد', 'Назаров Азизбек Бобир угли', 'Ассистент'),
            $this->staffMember('oil-gas-refining-technology-maxmudov-muxtor-jamolovich', 'Maxmudov Muxtor Jamolovich', 'DSc, Professor', 'محمودوف مختار جمالوفيتش', 'دكتور علوم، أستاذ', 'Махмудов Мухтор Жамолович', 'DSc, профессор'),
            $this->staffMember('oil-gas-refining-technology-hayitov-ruslan-rustamjonovich', 'Hayitov Ruslan Rustamjonovich', 'DSc, Professor', 'حاييتوف رسلان رستامجونوفيتش', 'دكتور علوم، أستاذ', 'Хайитов Руслан Рустамжонович', 'DSc, профессор'),
            $this->staffMember('oil-gas-refining-technology-bozorov-gayrat-rashidovich', "Bozorov G'ayrat Rashidovich", 'DSc, Professor', 'بازاروف غايرات رشيدوفيتش', 'دكتور علوم، أستاذ', 'Бозоров Гайрат Рашидович', 'DSc, профессор'),
            $this->staffMember('oil-gas-refining-technology-xojaqulov-aziz-fayzullaevich', "Xo'jaqulov Aziz Fayzullaevich", 'PhD, Associate Professor', 'خوجاقولوف عزيز فيض اللهيفيتش', 'دكتوراه، أستاذ مشارك', 'Хужакулов Азиз Файзуллаевич', 'PhD, доцент'),
            $this->staffMember('oil-gas-refining-technology-murodov-malik-negmurodovich', 'Murodov Malik Negmurodovich', 'Candidate of Technical Sciences, Associate Professor', 'مرادوف مالك نيغمورودوفيتش', 'مرشح في العلوم التقنية، أستاذ مشارك', 'Муродов Малик Негмуродович', 'Кандидат технических наук, доцент'),
            $this->staffMember('oil-gas-refining-technology-safarov-baxri-djumaevich', 'Safarov Baxri Djumaevich', 'Candidate of Technical Sciences, Associate Professor', 'سفاروف بخري جمائيفيتش', 'مرشح في العلوم التقنية، أستاذ مشارك', 'Сафаров Бахри Джумаевич', 'Кандидат технических наук, доцент'),
            $this->staffMember('oil-gas-refining-technology-tsukanov-maksim-nikolayevich', 'Tsukanov Maksim Nikolayevich', 'PhD, Associate Professor', 'تسوكانوف ماكسيم نيكولايفيتش', 'دكتوراه، أستاذ مشارك', 'Цуканов Максим Николаевич', 'PhD, доцент'),
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

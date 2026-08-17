<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $departmentId = DB::table('departments')->where('slug', 'oil-gas-engineering-upstream-downstream')->value('id');

        if (! $departmentId) {
            return;
        }

        DB::transaction(function () use ($departmentId) {
            $this->deletePlaceholderStaff($departmentId);
            $this->normalizeStaff($departmentId);
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function deletePlaceholderStaff(int $departmentId): void
    {
        $ids = DB::table('staff_profiles')
            ->where('department_id', $departmentId)
            ->whereIn('slug', [
                'oil-gas-engineering-upstream-downstream-0-prof-soxib-s-toshov',
                'oil-gas-engineering-upstream-downstream-1-dr-elmira-sh-ganiyeva',
                'oil-gas-engineering-upstream-downstream-2-farxod-b-nurullayev',
            ])
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        DB::table('staff_profile_translations')->whereIn('staff_profile_id', $ids)->delete();
        DB::table('staff_profiles')->whereIn('id', $ids)->delete();
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
                        'bio' => $translation['name'].' serves as '.$translation['position'].' in Oil and Gas Engineering, contributing to teaching, field practice, research, and academic development.',
                        'office' => 'Daily 14:00-16:00',
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
            $this->staffMember('oil-gas-engineering-upstream-downstream-sharipov-qaxramon-qandiyorovich', 'Sharipov Qaxramon Qandiyorovich', 'Head of Department', 'شاريبوف قهرمان قندياروفيتش', 'رئيس القسم', 'Шарипов Кахрамон Кандиёрович', 'Заведующий кафедрой'),
            $this->staffMember('oil-gas-engineering-upstream-downstream-bozorov-jorabek-toronovich', 'Bozorov Jo‘rabek To‘ronovich', 'Associate Professor, PhD', 'بازاروف جورابيك تورونوفيتش', 'أستاذ مشارك، دكتوراه', 'Бозоров Журабек Туронович', 'Доцент, PhD'),
            $this->staffMember('oil-gas-engineering-upstream-downstream-ochilov-abdurahim-abdurasulovich', 'Ochilov Abdurahim Abdurasulovich', 'Associate Professor, PhD', 'أوتشيلوف عبد الرحيم عبد الرسول أوفيتش', 'أستاذ مشارك، دكتوراه', 'Очилов Абдурахим Абдурасулович', 'Доцент, PhD'),
            $this->staffMember('oil-gas-engineering-upstream-downstream-rakhimov-bobomurod-rustamovich', 'Rakhimov Bobomurod Rustamovich', 'Assistant, PhD', 'رحيموف بوبومورود رستاموفيتش', 'مساعد، دكتوراه', 'Рахимов Бобомурод Рустамович', 'Ассистент, PhD'),
            $this->staffMember('oil-gas-engineering-upstream-downstream-obidov-hamid-olimovich', 'Obidov Hamid Olimovich', 'Senior Lecturer', 'عبيدوف حميد أوليموفيتش', 'محاضر أول', 'Обидов Хамид Олимович', 'Старший преподаватель'),
            $this->staffMember('oil-gas-engineering-upstream-downstream-sattorov-mirvohid-olimovich', 'Sattorov Mirvohid Olimovich', 'Senior Lecturer', 'ساتتوروف ميرفوهد أوليموفيتش', 'محاضر أول', 'Сатторов Мирвохид Олимович', 'Старший преподаватель'),
            $this->staffMember('oil-gas-engineering-upstream-downstream-yamaletdinova-aygul-akhmadovna', 'Yamaletdinova Aygul Akhmadovna', 'Assistant', 'ياماليتدينوفا أيغول أحمدوفنا', 'مساعدة', 'Ямалетдинова Айгуль Ахмадовна', 'Ассистент'),
            $this->staffMember('oil-gas-engineering-upstream-downstream-bokieva-shakhnoza-komilovna', 'Bokieva Shakhnoza Komilovna', 'Assistant', 'بوكييفا شاهنوزا كوميلوفنا', 'مساعدة', 'Бокиева Шахноза Комиловна', 'Ассистент'),
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

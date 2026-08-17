<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $departmentId = DB::table('departments')->where('slug', 'agricultural-products-storage-oil-fat-technology')->value('id');

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
                'agricultural-products-storage-oil-fat-technology-majidova-nargiza-kakhramonovna',
                'agricultural-products-storage-oil-fat-technology-0-dr-shahnoza-k-boltayeva',
                'agricultural-products-storage-oil-fat-technology-1-nurali-b-sodiqov',
                'agricultural-products-storage-oil-fat-technology-2-barno-o-kamolova',
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
        $oldSlug = 'agricultural-products-storage-oil-fat-technology-phd-senior-lecturer';
        $newSlug = 'agricultural-products-storage-oil-fat-technology-fayzullaev-asqar-rajabboevich';

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
                        'bio' => $translation['name'].' serves as '.$translation['position'].' in Agricultural Products Storage and Oil-Fat Technology, contributing to teaching, laboratory work, research, and academic development.',
                        'office' => 'Monday-Friday 14:00-16:00',
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
            $this->staffMember('agricultural-products-storage-oil-fat-technology-majidova-nargiza-kaxramonovna', 'Majidova Nargiza Kaxramonovna', 'Head of Department', 'ماجيدوفا نرجزة قهرمانوفنا', 'رئيسة القسم', 'Мажидова Наргиза Кахрамоновна', 'Заведующая кафедрой'),
            $this->staffMember('agricultural-products-storage-oil-fat-technology-majidov-qakhramon-khalimovich', 'Majidov Qakhramon Khalimovich', 'Doctor of Technical Sciences, Professor', 'ماجيدوف قهرمان خاليموفيتش', 'دكتور في العلوم التقنية، أستاذ', 'Мажидов Кахрамон Халимович', 'Доктор технических наук, профессор'),
            $this->staffMember('agricultural-products-storage-oil-fat-technology-ashurov-furkat-bakhromovich', 'Ashurov Furkat Bakhromovich', 'Candidate of Technical Sciences, Associate Professor', 'أشوروف فوركات باخروموفيتش', 'مرشح في العلوم التقنية، أستاذ مشارك', 'Ашуров Фуркат Бахромович', 'Кандидат технических наук, доцент'),
            $this->staffMember('agricultural-products-storage-oil-fat-technology-sabirova-nargiza-nusratovna', 'Sabirova Nargiza Nusratovna', 'PhD, Associate Professor', 'صابيروفا نرجزة نصرتوفنا', 'دكتوراه، أستاذة مشاركة', 'Сабирова Наргиза Нусратовна', 'PhD, доцент'),
            $this->staffMember('agricultural-products-storage-oil-fat-technology-oltiev-azim-toyqulovich', 'Oltiev Azim To‘yqulovich', 'PhD, Associate Professor', 'ألتييف عظيم تويقولوفتيش', 'دكتوراه، أستاذ مشارك', 'Олтиев Азим Туйкулович', 'PhD, доцент'),
            $this->staffMember('agricultural-products-storage-oil-fat-technology-fayzullaev-asqar-rajabboevich', 'Fayzullaev Asqar Rajabboevich', 'Senior Lecturer', 'فايزوللايف أسقار رجببوفيتش', 'محاضر أول', 'Файзуллаев Аскар Ражаббоевич', 'Старший преподаватель'),
            $this->staffMember('agricultural-products-storage-oil-fat-technology-mirzaeva-shokhista-usmonovna', 'Mirzaeva Shokhista Usmonovna', 'PhD, Senior Lecturer', 'ميرزايفا شاهستا عثمانوفنا', 'دكتوراه، محاضرة أولى', 'Мирзаева Шохиста Усмоновна', 'PhD, старший преподаватель'),
            $this->staffMember('agricultural-products-storage-oil-fat-technology-radjabova-lobar-ramazonovna', 'Radjabova Lobar Ramazonovna', 'PhD, Associate Professor', 'رجبوفا لوبار رمضانوفنا', 'دكتوراه، أستاذة مشاركة', 'Раджабова Лобар Рамазоновна', 'PhD, доцент'),
            $this->staffMember('agricultural-products-storage-oil-fat-technology-fatayeva-farodiba-rustamovna', 'Fatayeva Farodiba Rustamovna', 'Assistant', 'فاتاييفا فاروديبا رستاموفنا', 'مساعدة', 'Фатаева Фародиба Рустамовна', 'Ассистент'),
            $this->staffMember('agricultural-products-storage-oil-fat-technology-shodiev-bakhtiyor', 'Shodiev Bakhtiyor', 'Trainee Teacher', 'شوديف بختيار', 'مدرس متدرب', 'Шодиев Бахтиёр', 'Преподаватель-стажер'),
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

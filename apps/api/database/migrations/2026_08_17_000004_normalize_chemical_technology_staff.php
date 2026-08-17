<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $departmentId = DB::table('departments')->where('slug', 'chemical-technology')->value('id');

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
                'chemical-technology-akhmedov-voksi-nizomovich',
                'chemical-technology-0-prof-jamol-s-soliyev',
                'chemical-technology-1-dr-mavjuda-a-karimova',
                'chemical-technology-2-hasan-i-sharipov',
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
        $oldSlug = 'chemical-technology-teacher-trainee';
        $newSlug = 'chemical-technology-ikromov-ulugbek-gafur-ogli';

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
                        'bio' => $translation['name'].' serves as '.$translation['position'].' in Chemical Technology, contributing to teaching, laboratory work, research, and academic development.',
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
            $this->staffMember('chemical-technology-axmedov-voxid-nizomovich', 'Akhmedov Voxid Nizomovich', 'Head of Department', 'أحمدوف وحيد نظاموفيتش', 'رئيس القسم', 'Ахмедов Вохид Низомович', 'Заведующий кафедрой'),
            $this->staffMember('chemical-technology-makhmudov-rafiq-amonovich', 'Makhmudov Rafiq Amonovich', 'Doctor of Technical Sciences, Professor', 'محمودوف رفيق أمونوفيتش', 'دكتور في العلوم التقنية، أستاذ', 'Махмудов Рафик Амонович', 'Доктор технических наук, профессор'),
            $this->staffMember('chemical-technology-temirova-matlab-ibodovna', 'Temirova Matlab Ibodovna', 'Doctor of Technical Sciences, Professor', 'تيميروفا مطلب إيبودوفنا', 'دكتورة في العلوم التقنية، أستاذة', 'Темирова Матлаб Ибодовна', 'Доктор технических наук, профессор'),
            $this->staffMember('chemical-technology-haydarov-akhtam-amonovich', 'Haydarov Akhtam Amonovich', 'Doctor of Technical Sciences, Professor', 'حيدروف أختام أمونوفيتش', 'دكتور في العلوم التقنية، أستاذ', 'Хайдаров Ахтам Амонович', 'Доктор технических наук, профессор'),
            $this->staffMember('chemical-technology-olimov-babir-bahodir-ugly', 'Olimov Bobir Bahodir o‘g‘li', 'PhD, Associate Professor', 'أوليموف بوبير بهادير أوغلي', 'دكتوراه، أستاذ مشارك', 'Олимов Бобир Баходир угли', 'PhD, доцент'),
            $this->staffMember('chemical-technology-panoyev-nadir-shavkatovich', 'Panoyev Nadir Shavkatovich', 'PhD, Associate Professor', 'بانويف نادر شفكاتوفيتش', 'دكتوراه، أستاذ مشارك', 'Паноев Надир Шавкатович', 'PhD, доцент'),
            $this->staffMember('chemical-technology-sadikova-muxayyo-murodovna', 'Sadikova Muxayyo Murodovna', 'PhD, Associate Professor', 'صاديقوفا موخايو مرادوفنا', 'دكتوراه، أستاذة مشاركة', 'Садикова Мухайё Муродовна', 'PhD, доцент'),
            $this->staffMember('chemical-technology-sharipov-begmurod-sharopovich', 'Sharipov Begmurod Sharopovich', 'PhD, Associate Professor', 'شاريبوف بيغمورود شاروبوفيتش', 'دكتوراه، أستاذ مشارك', 'Шарипов Бегмурод Шаропович', 'PhD, доцент'),
            $this->staffMember('chemical-technology-umarov-bobur-nosir-ogli', "Umarov Bobur Nosir o'g'li", 'PhD, Associate Professor', 'عمرُوف بوبور ناصر أوغلي', 'دكتوراه، أستاذ مشارك', 'Умаров Бобур Носир угли', 'PhD, доцент'),
            $this->staffMember('chemical-technology-khuzhakulova-dilbar-jorakulovna', 'Khuzhakulova Dilbar Jorakulovna', 'PhD, Associate Professor', 'خوجاقولوفا ديلبر جوراقولوفنا', 'دكتوراه، أستاذة مشاركة', 'Хужакулова Дилбар Жоракуловна', 'PhD, доцент'),
            $this->staffMember('chemical-technology-narzullaeva-aziza-murodillayevna', 'Narzullaeva Aziza Murodillayevna', 'PhD, Associate Professor', 'نرزوللايفا عزيزة مراديللايفنا', 'دكتوراه، أستاذة مشاركة', 'Нарзуллаева Азиза Муродиллаевна', 'PhD, доцент'),
            $this->staffMember('chemical-technology-voxidov-erkin-aliyevich', 'Voxidov Erkin Aliyevich', 'PhD, Associate Professor', 'وحيدوف إركين علييفيتش', 'دكتوراه، أستاذ مشارك', 'Вохидов Эркин Алиевич', 'PhD, доцент'),
            $this->staffMember('chemical-technology-joraeva-laylo-raxmatillaevna', 'Jo‘raeva Laylo Raxmatillaevna', 'Assistant', 'جوراييفا لايلو رحمتيللايفنا', 'مساعدة', 'Жураева Лайло Рахматиллаевна', 'Ассистент'),
            $this->staffMember('chemical-technology-sharipova-nasiba-oktamovna', 'Sharipova Nasiba O‘ktamovna', 'Assistant', 'شاريبوفا ناسيبا أوكتاموفنا', 'مساعدة', 'Шарипова Насиба Уктамовна', 'Ассистент'),
            $this->staffMember('chemical-technology-raxmatov-marat-salimovich', 'Raxmatov Marat Salimovich', 'Assistant', 'رحمتوف مرات سالموفيتش', 'مساعد', 'Рахматов Марат Салимович', 'Ассистент'),
            $this->staffMember('chemical-technology-sadikova-mashhura-idilloevna', 'Sadikova Mashhura Idilloevna', 'Assistant', 'صاديقوفا مشهورة إيديللوفنا', 'مساعدة', 'Садикова Машхура Идиллоевна', 'Ассистент'),
            $this->staffMember('chemical-technology-gafurova-gulnoz-alixonovna', 'G‘afurova Gulnoz Alixonovna', 'Assistant', 'غافوروفا غولنوز عليخونوفنا', 'مساعدة', 'Гафурова Гулноз Алихоновна', 'Ассистент'),
            $this->staffMember('chemical-technology-jumaev-jabbor-hamroqulovich', 'Jumaev Jabbor Hamroqulovich', 'Assistant', 'جمعايف جبار حمروقولوفيتش', 'مساعد', 'Жумаев Жаббор Хамрокулович', 'Ассистент'),
            $this->staffMember('chemical-technology-ikromov-ulugbek-gafur-ogli', 'Ikromov Ulugbek Gafur oglu', 'Trainee Teacher', 'إكروموف أولوغبيك غافور أوغلي', 'مدرس متدرب', 'Икромов Улугбек Гафур угли', 'Преподаватель-стажер'),
            $this->staffMember('chemical-technology-kurbonova-salima-shukhratovna', 'Kurbonova Salima Shukhratovna', 'Trainee Teacher', 'قربونوفا سليمة شوخراتوفنا', 'مدرسة متدربة', 'Курбонова Салима Шухратовна', 'Преподаватель-стажер'),
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

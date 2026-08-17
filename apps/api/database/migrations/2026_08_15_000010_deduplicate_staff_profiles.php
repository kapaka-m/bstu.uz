<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('staff_profiles') || ! Schema::hasTable('staff_profile_translations')) {
            return;
        }

        DB::transaction(function () {
            $this->fixSwappedNameAndPositionRows();
            $this->deduplicateProfiles();
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function fixSwappedNameAndPositionRows(): void
    {
        $rows = DB::table('staff_profiles as s')
            ->join('staff_profile_translations as en', function ($join) {
                $join->on('en.staff_profile_id', '=', 's.id')->where('en.locale', 'en');
            })
            ->select('s.id', 's.department_id', 'en.full_name', 'en.position')
            ->orderBy('s.id')
            ->get();

        foreach ($rows as $row) {
            if (! $this->looksLikeRole($row->full_name) || ! $this->looksLikePersonName($row->position)) {
                continue;
            }

            $personName = $this->cleanPersonName($row->position);
            $role = $this->normalizeRole($row->full_name);

            if ($personName === '' || $role === '') {
                continue;
            }

            $departmentName = $this->departmentName((int) $row->department_id);

            foreach ($this->locales() as $locale) {
                DB::table('staff_profile_translations')->updateOrInsert(
                    ['staff_profile_id' => $row->id, 'locale' => $locale],
                    [
                        'full_name' => $personName,
                        'position' => $this->localizedRole($role, $locale),
                        'bio' => $this->localizedBio($personName, $this->localizedRole($role, $locale), $departmentName, $locale),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }

    private function deduplicateProfiles(): void
    {
        $profiles = DB::table('staff_profiles as s')
            ->leftJoin('staff_profile_translations as en', function ($join) {
                $join->on('en.staff_profile_id', '=', 's.id')->where('en.locale', 'en');
            })
            ->select(
                's.id',
                's.slug',
                's.faculty_id',
                's.department_id',
                's.photo',
                's.email',
                's.phone',
                's.sort_order',
                's.is_active',
                'en.full_name',
                'en.position'
            )
            ->orderBy('s.id')
            ->get()
            ->filter(fn ($profile) => $this->normalizedName($profile->full_name) !== '')
            ->groupBy(fn ($profile) => implode('|', [
                $profile->faculty_id ?: '0',
                $profile->department_id ?: '0',
                $this->normalizedName($profile->full_name),
            ]));

        foreach ($profiles as $group) {
            if ($group->count() < 2) {
                continue;
            }

            $canonical = $this->chooseCanonicalProfile($group);
            $duplicates = $group->reject(fn ($profile) => (int) $profile->id === (int) $canonical->id);

            foreach ($duplicates as $duplicate) {
                $this->mergeProfile((int) $canonical->id, (int) $duplicate->id);
            }
        }
    }

    private function chooseCanonicalProfile(Collection $profiles): object
    {
        return $profiles
            ->sortByDesc(fn ($profile) => $this->canonicalScore($profile))
            ->values()
            ->first();
    }

    private function canonicalScore(object $profile): int
    {
        $slug = Str::lower((string) $profile->slug);
        $score = 0;

        if ((bool) $profile->is_active) {
            $score += 100;
        }

        if (preg_match('/faculty-of-[a-z0-9-]+-(dean|academic|youth)-/u', $slug)) {
            $score += 70;
        }

        if (! preg_match('/-\d+-/u', $slug)) {
            $score += 45;
        }

        if (preg_match('/^(faculty-of|[a-z0-9-]+)-(?!\\d)/u', $slug)) {
            $score += 10;
        }

        if (! empty($profile->photo)) {
            $score += 8;
        }

        if (! empty($profile->email)) {
            $score += 4;
        }

        if (! empty($profile->phone)) {
            $score += 4;
        }

        return $score + min((int) $profile->id, 999);
    }

    private function mergeProfile(int $canonicalId, int $duplicateId): void
    {
        $canonical = DB::table('staff_profiles')->where('id', $canonicalId)->first();
        $duplicate = DB::table('staff_profiles')->where('id', $duplicateId)->first();

        if (! $canonical || ! $duplicate) {
            return;
        }

        $updates = [];
        foreach (['photo', 'email', 'phone'] as $column) {
            if (empty($canonical->{$column}) && ! empty($duplicate->{$column})) {
                $updates[$column] = $duplicate->{$column};
            }
        }

        $updates['sort_order'] = min((int) ($canonical->sort_order ?? 100), (int) ($duplicate->sort_order ?? 100));
        $updates['is_active'] = (bool) $canonical->is_active || (bool) $duplicate->is_active;
        $updates['updated_at'] = now();

        DB::table('staff_profiles')->where('id', $canonicalId)->update($updates);

        foreach ($this->locales() as $locale) {
            $current = DB::table('staff_profile_translations')
                ->where('staff_profile_id', $canonicalId)
                ->where('locale', $locale)
                ->first();
            $candidate = DB::table('staff_profile_translations')
                ->where('staff_profile_id', $duplicateId)
                ->where('locale', $locale)
                ->first();

            if (! $candidate) {
                continue;
            }

            if (! $current) {
                DB::table('staff_profile_translations')->where('id', $candidate->id)->update([
                    'staff_profile_id' => $canonicalId,
                    'updated_at' => now(),
                ]);
                continue;
            }

            $translationUpdates = [];
            foreach (['full_name', 'position', 'bio', 'office'] as $column) {
                if ($this->translationNeedsReplacement($current->{$column}, $candidate->{$column}, $column)) {
                    $translationUpdates[$column] = $candidate->{$column};
                }
            }

            if ($translationUpdates !== []) {
                DB::table('staff_profile_translations')
                    ->where('id', $current->id)
                    ->update($translationUpdates + ['updated_at' => now()]);
            }
        }

        DB::table('staff_profile_translations')->where('staff_profile_id', $duplicateId)->delete();
        DB::table('staff_profiles')->where('id', $duplicateId)->delete();
    }

    private function translationNeedsReplacement(?string $current, ?string $candidate, string $column): bool
    {
        if ($candidate === null || trim($candidate) === '') {
            return false;
        }

        if ($current === null || trim($current) === '') {
            return true;
        }

        if ($column === 'full_name' && $this->looksLikeRole($current) && $this->looksLikePersonName($candidate)) {
            return true;
        }

        return false;
    }

    private function normalizedName(?string $name): string
    {
        $name = $this->cleanPersonName((string) $name);
        $name = preg_replace('/^(dr|prof|assoc\\. prof|phd)\\.?\\s+/iu', '', $name);
        $name = Str::lower($name);
        $name = str_replace(['‘', '’', '`', 'ʼ', 'ʻ'], "'", $name);
        $name = preg_replace('/\\s+/u', ' ', $name);

        return trim($name ?? '');
    }

    private function cleanPersonName(?string $name): string
    {
        $name = html_entity_decode((string) $name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $name = strip_tags($name);
        $name = preg_replace('/\\s*\\/?(?:strong|стронг|سترونغ)\\s*>/iu', '', $name);
        $name = preg_replace('/^\\.+\\s*/u', '', $name);
        $name = preg_replace('/\\s+/u', ' ', $name);

        return trim($name ?? '');
    }

    private function looksLikeRole(?string $value): bool
    {
        $value = Str::lower($this->cleanPersonName($value));

        if ($value === '') {
            return false;
        }

        foreach ([
            'senior lecturer',
            'senior teacher',
            'teacher trainee',
            'trainee teacher',
            'katta',
            'dotsent',
            'assistent',
            'assistant',
            'doctorant',
            'doktorant',
            'texnika fanlari',
            'qishloq',
            'phd',
        ] as $roleNeedle) {
            if (str_contains($value, $roleNeedle)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikePersonName(?string $value): bool
    {
        $value = $this->cleanPersonName($value);

        if ($value === '' || str_contains($value, ' - ') || str_contains($value, ',')) {
            return false;
        }

        $parts = preg_split('/\\s+/u', $value) ?: [];

        if (count($parts) < 2 || count($parts) > 5) {
            return false;
        }

        return preg_match('/\\p{Lu}/u', $value) === 1;
    }

    private function normalizeRole(?string $role): string
    {
        $role = Str::lower($this->cleanPersonName($role));

        return match (true) {
            str_contains($role, 'senior lecturer'), str_contains($role, 'senior teacher'), str_contains($role, 'katta') => 'Senior Lecturer',
            str_contains($role, 'teacher trainee'), str_contains($role, 'trainee teacher') => 'Trainee Teacher',
            str_contains($role, 'doctorant'), str_contains($role, 'doktorant') => 'Doctoral Student',
            str_contains($role, 'assistant'), str_contains($role, 'assistent') => 'Assistant',
            str_contains($role, 'dotsent') => 'Associate Professor',
            default => 'Academic Staff',
        };
    }

    private function localizedRole(string $role, string $locale): string
    {
        $roles = [
            'Senior Lecturer' => [
                'en' => 'Senior Lecturer',
                'uz' => 'Katta o‘qituvchi',
                'ru' => 'Старший преподаватель',
                'ar' => 'محاضر أول',
            ],
            'Trainee Teacher' => [
                'en' => 'Trainee Teacher',
                'uz' => 'Stajyor-o‘qituvchi',
                'ru' => 'Преподаватель-стажер',
                'ar' => 'مدرس متدرب',
            ],
            'Doctoral Student' => [
                'en' => 'Doctoral Student',
                'uz' => 'Doktorant',
                'ru' => 'Докторант',
                'ar' => 'باحث دكتوراه',
            ],
            'Assistant' => [
                'en' => 'Assistant',
                'uz' => 'Assistent',
                'ru' => 'Ассистент',
                'ar' => 'مساعد',
            ],
            'Associate Professor' => [
                'en' => 'Associate Professor',
                'uz' => 'Dotsent',
                'ru' => 'Доцент',
                'ar' => 'أستاذ مشارك',
            ],
            'Academic Staff' => [
                'en' => 'Academic Staff',
                'uz' => 'Kafedra professor-o‘qituvchisi',
                'ru' => 'Преподаватель кафедры',
                'ar' => 'عضو هيئة تدريس في القسم',
            ],
        ];

        return $roles[$role][$locale] ?? $roles[$role]['en'] ?? $role;
    }

    private function localizedBio(string $name, string $role, string $departmentName, string $locale): string
    {
        return match ($locale) {
            'uz' => "{$name} {$departmentName} tarkibida {$role} sifatida faoliyat yuritadi.",
            'ru' => "{$name} работает в подразделении «{$departmentName}» в должности «{$role}».",
            'ar' => "{$name} يعمل/تعمل في {$departmentName} بصفة {$role}.",
            default => "{$name} serves as {$role} in {$departmentName}.",
        };
    }

    private function departmentName(int $departmentId): string
    {
        if ($departmentId <= 0) {
            return 'the university';
        }

        return DB::table('department_translations')
            ->where('department_id', $departmentId)
            ->where('locale', 'en')
            ->value('name') ?: 'the department';
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

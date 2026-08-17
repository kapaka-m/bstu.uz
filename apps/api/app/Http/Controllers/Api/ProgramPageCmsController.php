<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Locale;
use App\Models\ProgramPageSetting;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProgramPageCmsController extends Controller
{
    use ApiResponse;

    private array $labelFields = [
        'back_to_programs_label',
        'course_curriculum_label',
        'courses_label',
        'year_label',
        'semester_label',
        'admission_requirements_label',
        'documents_label',
        'quick_facts_label',
        'program_code_label',
        'degree_level_label',
        'duration_label',
        'years_label',
        'language_of_instruction_label',
        'study_mode_label',
        'tuition_fee_label',
        'intake_period_label',
        'intake_date_label',
        'parent_faculty_label',
        'parent_department_label',
        'program_coordinator_label',
        'academic_staff_label',
        'faculty_helpdesk_label',
        'faculty_helpdesk_description',
        'contact_university_label',
        'career_opportunities_label',
        'apply_now_label',
        'apply_description',
        'not_found_title_label',
        'not_found_description',
        'no_details_label',
        'degree_bachelor_label',
        'degree_master_label',
        'degree_phd_label',
        'mode_full_time_label',
        'mode_part_time_label',
        'mode_evening_label',
        'mode_distance_label',
        'language_english_label',
        'language_uzbek_label',
        'language_russian_label',
        'language_arabic_label',
    ];

    public function publicShow(Request $request)
    {
        $locale = $this->requestLocale($request);
        $fallback = $this->fallbackLocale();

        $payload = Cache::remember(
            'public_api:v'.Cache::get('public_content_cache_version', '1').':program-page-cms:'.$locale,
            now()->addSeconds((int) config('cache.public_api_ttl', 600)),
            function () use ($locale, $fallback) {
                $setting = ProgramPageSetting::query()
                    ->with('translations')
                    ->where('key', 'main')
                    ->where('is_active', true)
                    ->first();

                return $setting ? $this->formatPublicPayload($setting, $locale, $fallback) : [
                    'locale' => $locale,
                    'settings' => [],
                    'labels' => [],
                ];
            },
        );

        return $this->successResponse($payload, 'Program page CMS content retrieved successfully');
    }

    public function adminShow()
    {
        $setting = ProgramPageSetting::with('translations')->firstOrCreate(
            ['key' => 'main'],
            ['is_active' => true, 'settings' => []],
        );

        return $this->successResponse([
            'locales' => $this->localeCodes(),
            'setting' => $setting,
        ], 'Program page CMS content retrieved');
    }

    public function adminUpdate(Request $request)
    {
        $rules = [
            'setting' => 'required|array',
            'setting.key' => 'nullable|string|max:100',
            'setting.is_active' => 'boolean',
            'setting.settings' => 'nullable|array',
            'setting.translations' => 'required|array',
        ];

        foreach ($this->labelFields as $field) {
            $rules["setting.translations.*.$field"] = 'nullable|string';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $validated = $validator->validated()['setting'];

        DB::transaction(function () use ($validated) {
            $setting = ProgramPageSetting::firstOrCreate(
                ['key' => $validated['key'] ?? 'main'],
                ['is_active' => true, 'settings' => []],
            );

            $setting->update([
                'is_active' => $validated['is_active'] ?? true,
                'settings' => $validated['settings'] ?? [],
            ]);

            foreach ($validated['translations'] as $locale => $fields) {
                $payload = array_intersect_key($fields, array_flip($this->labelFields));
                $setting->translations()->updateOrCreate(
                    ['locale' => $locale],
                    $payload + ['locale' => $locale],
                );
            }
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());

        return $this->adminShow();
    }

    private function formatPublicPayload(ProgramPageSetting $setting, string $locale, string $fallback): array
    {
        $translation = $this->translation($setting->translations, $locale, $fallback);
        $labels = [];

        foreach ($this->labelFields as $field) {
            $publicKey = preg_replace('/_label$/', '', $field);
            $publicKey = str_replace('_description', '_description', $publicKey);
            $labels[$publicKey] = $translation->{$field} ?? '';
        }

        return [
            'id' => $setting->id,
            'locale' => $locale,
            'is_active' => (bool) $setting->is_active,
            'settings' => $setting->settings ?: [],
            'labels' => $labels,
        ];
    }

    private function translation(Collection $translations, string $locale, string $fallback): ?object
    {
        return $translations->firstWhere('locale', $locale)
            ?: $translations->firstWhere('locale', $fallback)
            ?: $translations->first();
    }

    private function requestLocale(Request $request): string
    {
        $locale = $request->query('locale')
            ?: $request->header('X-Locale')
            ?: $request->header('Accept-Language');
        $supported = $this->localeCodes();

        if ($locale) {
            $locale = strtolower(trim(explode(',', str_replace('_', '-', $locale))[0]));

            if (in_array($locale, $supported, true)) {
                return $locale;
            }

            $primary = explode('-', $locale)[0] ?? '';
            if ($primary && in_array($primary, $supported, true)) {
                return $primary;
            }
        }

        return $this->fallbackLocale();
    }

    private function localeCodes(): array
    {
        $codes = Locale::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('code')
            ->map(fn ($code) => strtolower(str_replace('_', '-', trim((string) $code))))
            ->filter()
            ->values()
            ->all();

        return $codes ?: array_values(array_filter([
            config('app.fallback_locale'),
            config('app.locale'),
        ]));
    }

    private function fallbackLocale(): string
    {
        return $this->localeCodes()[0]
            ?? config('app.fallback_locale')
            ?? config('app.locale')
            ?? 'en';
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DepartmentPageSetting;
use App\Models\Locale;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DepartmentPageCmsController extends Controller
{
    use ApiResponse;

    private array $labelFields = [
        'home_label',
        'faculties_label',
        'quick_contact_label',
        'back_to_faculty_label',
        'head_of_department_label',
        'history_label',
        'prepared_specialists_label',
        'subjects_label',
        'staff_label',
        'publications_label',
        'research_label',
        'cooperation_label',
        'activities_label',
        'bachelor_label',
        'master_label',
        'doctoral_label',
        'programs_label',
        'bachelor_subjects_label',
        'master_subjects_label',
        'gallery_label',
        'conference_papers_label',
        'scopus_web_of_science_label',
        'textbooks_manuals_label',
        'textbooks_manuals_monographs_label',
        'monographs_label',
        'articles_label',
        'not_found_title_label',
        'not_found_description',
    ];

    public function publicShow(Request $request)
    {
        $locale = $this->requestLocale($request);
        $fallback = $this->fallbackLocale();

        $payload = Cache::remember(
            'public_api:v'.Cache::get('public_content_cache_version', '1').':department-page-cms:'.$locale,
            now()->addSeconds((int) config('cache.public_api_ttl', 600)),
            function () use ($locale, $fallback) {
                $setting = DepartmentPageSetting::query()
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

        return $this->successResponse($payload, 'Department page CMS content retrieved successfully');
    }

    public function adminShow()
    {
        $setting = DepartmentPageSetting::with('translations')->firstOrCreate(
            ['key' => 'main'],
            ['is_active' => true, 'settings' => []],
        );

        return $this->successResponse([
            'locales' => $this->localeCodes(),
            'setting' => $setting,
        ], 'Department page CMS content retrieved');
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
            $setting = DepartmentPageSetting::firstOrCreate(
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

    private function formatPublicPayload(DepartmentPageSetting $setting, string $locale, string $fallback): array
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

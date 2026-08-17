<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FacultyPageSetting;
use App\Models\Locale;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FacultyPageCmsController extends Controller
{
    use ApiResponse;

    private array $labelFields = [
        'home_label',
        'faculties_label',
        'departments_label',
        'bachelor_programs_label',
        'master_specializations_label',
        'contact_label',
        'overview_label',
        'leadership_label',
        'learn_more_label',
        'head_of_department_label',
        'phone_label',
        'email_label',
        'quick_department_links_label',
        'dean_contact_label',
        'deputy_dean_contacts_label',
        'industry_cooperation_label',
        'academic_pathways_label',
        'leadership_description',
        'departments_description',
        'bachelor_description',
        'master_description',
        'contact_description',
        'not_found_title_label',
        'not_found_description',
    ];

    public function publicShow(Request $request)
    {
        $locale = $this->requestLocale($request);
        $fallback = $this->fallbackLocale();

        $payload = Cache::remember(
            'public_api:v'.Cache::get('public_content_cache_version', '1').':faculty-page-cms:'.$locale,
            now()->addSeconds((int) config('cache.public_api_ttl', 600)),
            function () use ($locale, $fallback) {
                $setting = FacultyPageSetting::query()
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

        return $this->successResponse($payload, 'Faculty page CMS content retrieved successfully');
    }

    public function adminShow()
    {
        $setting = FacultyPageSetting::with('translations')->firstOrCreate(
            ['key' => 'main'],
            ['is_active' => true, 'settings' => []],
        );

        return $this->successResponse([
            'locales' => $this->localeCodes(),
            'setting' => $setting,
        ], 'Faculty page CMS content retrieved');
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
            $setting = FacultyPageSetting::firstOrCreate(
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

    private function formatPublicPayload(FacultyPageSetting $setting, string $locale, string $fallback): array
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

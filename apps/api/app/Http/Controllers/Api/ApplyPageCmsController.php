<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApplyPage;
use App\Models\Locale;
use App\Models\TranslationKey;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ApplyPageCmsController extends Controller
{
    use ApiResponse;

    public function publicShow(Request $request)
    {
        $locale = $this->requestLocale($request);
        $this->ensureDefaultPage();

        $payload = Cache::remember(
            'public_api:v'.Cache::get('public_content_cache_version', '1').':apply-page:'.$locale,
            now()->addSeconds((int) config('cache.public_api_ttl', 600)),
            fn () => $this->localizedPayload($locale)
        );

        if (! $payload) {
            return $this->errorResponse('Apply page content not found', 404);
        }

        return $this->successResponse($payload, 'Apply page content retrieved successfully');
    }

    public function adminShow()
    {
        $page = $this->ensureDefaultPage();

        return $this->successResponse($this->adminPayload($page), 'Apply page CMS content retrieved');
    }

    public function adminUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'is_published' => 'boolean',
            'settings' => 'nullable|array',
            'translations' => 'required|array',
            'translations.*.locale' => 'required|string|max:35',
            'translations.*.content' => 'required|array',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        $validated = $validator->validated();

        DB::transaction(function () use ($validated) {
            $page = ApplyPage::firstOrCreate(['key' => 'main'], ['is_published' => true]);
            $page->update([
                'is_published' => $validated['is_published'] ?? true,
                'settings' => $validated['settings'] ?? [],
            ]);

            foreach ($validated['translations'] as $translation) {
                $page->translations()->updateOrCreate(
                    ['locale' => $translation['locale']],
                    ['content' => $this->normalizeContent($translation['content'])]
                );
            }
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());

        return $this->adminShow();
    }

    protected function localizedPayload(string $locale): ?array
    {
        $page = ApplyPage::with('translations')
            ->where('key', 'main')
            ->where('is_published', true)
            ->first();

        if (! $page) {
            return null;
        }

        $fallback = $this->fallbackLocale();
        $translation = $page->translations->firstWhere('locale', $locale)
            ?: $page->translations->firstWhere('locale', $fallback)
            ?: $page->translations->firstWhere('locale', 'en')
            ?: $page->translations->first();

        return [
            'key' => $page->key,
            'settings' => $page->settings ?: [],
            'content' => $this->normalizeContent($translation?->content ?: $this->defaultContent($locale)),
        ];
    }

    protected function adminPayload(ApplyPage $page): array
    {
        $page->loadMissing('translations');
        $locales = $this->localeCodes();

        return [
            'key' => $page->key,
            'is_published' => (bool) $page->is_published,
            'settings' => $page->settings ?: [],
            'locales' => $locales,
            'translations' => collect($locales)->map(function (string $locale) use ($page) {
                $translation = $page->translations->firstWhere('locale', $locale);

                return [
                    'locale' => $locale,
                    'content' => $this->normalizeContent($translation?->content ?: $this->defaultContent($locale)),
                ];
            })->values()->all(),
        ];
    }

    protected function ensureDefaultPage(): ApplyPage
    {
        $page = ApplyPage::firstOrCreate(
            ['key' => 'main'],
            ['is_published' => true, 'settings' => []]
        );

        $page->loadMissing('translations');
        $locales = $this->localeCodes();

        foreach ($locales as $locale) {
            $page->translations()->firstOrCreate(
                ['locale' => $locale],
                ['content' => $this->defaultContent($locale)]
            );
        }

        return $page->fresh('translations');
    }

    protected function defaultContent(string $locale): array
    {
        $content = $this->englishDefaults();
        $fallback = $this->fallbackLocale();

        $keys = TranslationKey::with(['values' => function ($query) use ($locale, $fallback) {
            $query->whereIn('locale', array_unique(array_filter([$locale, $fallback, 'en'])));
        }])->where('group', 'initialApplication')->get();

        foreach ($keys as $key) {
            $value = $key->values->firstWhere('locale', $locale)?->value
                ?? $key->values->firstWhere('locale', $fallback)?->value
                ?? $key->values->firstWhere('locale', 'en')?->value;

            if ($value !== null && $value !== '') {
                Arr::set($content, $key->key, $value);
            }
        }

        return $this->normalizeContent($content);
    }

    protected function normalizeContent(array $content): array
    {
        return array_replace_recursive($this->englishDefaults(), $content);
    }

    protected function englishDefaults(): array
    {
        return [
            'title' => 'International Student Application',
            'subtitle' => 'Create your student account and submit the first application step.',
            'personal' => 'Personal',
            'academic' => 'Academic',
            'review' => 'Review',
            'passportHint' => 'Enter your passport and contact details exactly as they appear in official documents.',
            'fullName' => 'Full name in English',
            'fullNamePlaceholder' => 'AS WRITTEN IN PASSPORT',
            'birthDate' => 'Date of birth',
            'birthDatePlaceholder' => 'Select date',
            'countryBirth' => 'Country of birth',
            'countryBirthPlaceholder' => 'Select country',
            'placeBirth' => 'Place of birth',
            'placeBirthPlaceholder' => 'City or region',
            'nationality' => 'Nationality',
            'nationalityPlaceholder' => 'Select nationality',
            'gender' => 'Gender',
            'genderPlaceholder' => 'Select gender',
            'passportNumber' => 'Passport number',
            'passportNumberPlaceholder' => 'Passport number',
            'passportType' => 'Passport type',
            'passportTypePlaceholder' => 'Select passport type',
            'issueDate' => 'Issue date',
            'issueDatePlaceholder' => 'Select date',
            'expiryDate' => 'Expiry date',
            'expiryDatePlaceholder' => 'Select date',
            'issuingCountry' => 'Issuing country',
            'issuingCountryPlaceholder' => 'Select country',
            'placeIssue' => 'Place of issue',
            'placeIssuePlaceholder' => 'City or authority',
            'primaryPhone' => 'Primary phone',
            'primaryPhonePlaceholder' => '+998901234567',
            'messenger' => 'Preferred messenger',
            'messengerPlaceholder' => 'Select messenger',
            'telegram' => 'Telegram username',
            'telegramPlaceholder' => '@username',
            'alternativePhone' => 'Alternative phone',
            'alternativePhonePlaceholder' => '+998901234567',
            'degree' => 'Degree level',
            'degreePlaceholder' => 'Select degree',
            'studentType' => 'Student type',
            'studentTypePlaceholder' => 'Select type',
            'faculty' => 'Faculty',
            'facultyPlaceholder' => 'Select faculty',
            'program' => 'Program',
            'programPlaceholder' => 'Select program',
            'educationType' => 'Education type',
            'educationTypePlaceholder' => 'Select education type',
            'language' => 'Language of study',
            'languagePlaceholder' => 'Select language',
            'intake' => 'Intended intake',
            'intakePlaceholder' => 'Select intake',
            'duration' => 'Duration',
            'yearsLabel' => 'years',
            'transferNote' => 'Transfer applicants may be asked to upload additional academic records after account creation.',
            'email' => 'Email',
            'emailPlaceholder' => 'you@example.com',
            'password' => 'Password',
            'passwordPlaceholder' => 'At least 8 characters',
            'confirmPassword' => 'Confirm password',
            'confirmPasswordPlaceholder' => 'Repeat password',
            'personalInfo' => 'Personal information',
            'academicInfo' => 'Academic information',
            'accountInfo' => 'Account information',
            'editPersonal' => 'Edit personal',
            'editAcademic' => 'Edit academic',
            'editLogin' => 'Edit login',
            'terms' => 'I agree to the admission terms and privacy policy.',
            'confirm' => 'I confirm that all information provided is accurate.',
            'back' => 'Back',
            'next' => 'Next',
            'submit' => 'Submit application',
            'submitting' => 'Submitting...',
            'successTitle' => 'Application started successfully',
            'successText' => 'Your student account has been created and the first application step has been submitted.',
            'applicationNumber' => 'Application number',
            'dashboard' => 'Go to student dashboard',
            'required' => 'This field is required.',
            'invalidName' => 'Use Latin letters only.',
            'expiredPassport' => 'Passport must be valid.',
            'expiryAfterIssue' => 'Expiry date must be after issue date.',
            'invalidPhone' => 'Use international format, for example +998901234567.',
            'duplicatePhone' => 'Alternative phone must be different.',
            'unavailableProgram' => 'This option is unavailable for the selected program.',
            'invalidEmail' => 'Enter a valid email address.',
            'passwordWeak' => 'Password must be at least 8 characters and include letters and numbers.',
            'passwordMatch' => 'Passwords do not match.',
            'apiFailed' => 'Could not submit application. Please try again.',
            'options' => [
                'gender' => ['male' => 'Male', 'female' => 'Female'],
                'passport_type' => ['ordinary' => 'Ordinary', 'biometric' => 'Biometric'],
                'messenger' => ['telegram' => 'Telegram', 'whatsapp' => 'WhatsApp', 'both' => 'Telegram and WhatsApp'],
                'degree_level' => ['bachelor' => 'Bachelor', 'master' => 'Master', 'phd' => 'PhD', 'doctorate' => 'Doctorate'],
                'student_type' => ['new' => 'New student', 'transfer' => 'Transfer student'],
                'education_type' => ['full_time' => 'Full-time', 'part_time' => 'Part-time', 'distance' => 'Distance'],
                'study_language' => ['uzbek' => 'Uzbek', 'russian' => 'Russian', 'english' => 'English'],
            ],
        ];
    }

    protected function localeCodes(): array
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

    protected function requestLocale(Request $request): string
    {
        $locale = $this->normalizeRequestedLocale((string) (
            $request->query('locale')
            ?: $request->header('X-Locale')
            ?: $request->header('Accept-Language')
        ));

        if (in_array($locale, $this->localeCodes(), true)) {
            return $locale;
        }

        $primary = explode('-', $locale)[0] ?? '';

        return $primary && in_array($primary, $this->localeCodes(), true)
            ? $primary
            : $this->fallbackLocale();
    }

    protected function normalizeRequestedLocale(string $locale): string
    {
        return strtolower(trim(explode(',', str_replace('_', '-', $locale))[0]));
    }

    protected function fallbackLocale(): string
    {
        return Locale::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->value('code') ?: 'en';
    }
}

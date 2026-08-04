<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuthEmailTemplate;
use App\Models\AuthPage;
use App\Models\Locale;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AuthCmsController extends Controller
{
    use ApiResponse;

    public function publicIndex(Request $request)
    {
        $locale = $this->requestLocale($request);
        $fallback = $this->fallbackLocale();

        $payload = Cache::remember(
            'public_api:v'.Cache::get('public_content_cache_version', '1').':auth-cms:'.$locale,
            now()->addSeconds((int) config('cache.public_api_ttl', 600)),
            function () use ($locale, $fallback) {
                return [
                    'pages' => AuthPage::query()
                        ->with('translations')
                        ->where('is_active', true)
                        ->orderBy('id')
                        ->get()
                        ->mapWithKeys(fn (AuthPage $page) => [
                            $page->page_key => $this->formatPage($page, $locale, $fallback),
                        ])
                        ->all(),
                ];
            },
        );

        return $this->successResponse($payload, 'Auth CMS content retrieved successfully');
    }

    public function adminShow()
    {
        return $this->successResponse([
            'locales' => $this->localeCodes(),
            'pages' => AuthPage::with('translations')->orderBy('id')->get(),
            'email_templates' => AuthEmailTemplate::with('translations')->orderBy('id')->get(),
        ], 'Auth CMS content retrieved');
    }

    public function adminUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pages' => 'required|array',
            'pages.*.page_key' => 'required|string|max:100',
            'pages.*.is_active' => 'boolean',
            'pages.*.settings' => 'nullable|array',
            'pages.*.translations' => 'required|array',
            'pages.*.translations.*.title' => 'nullable|string|max:255',
            'pages.*.translations.*.subtitle' => 'nullable|string',
            'pages.*.translations.*.email_label' => 'nullable|string|max:255',
            'pages.*.translations.*.email_placeholder' => 'nullable|string|max:255',
            'pages.*.translations.*.password_label' => 'nullable|string|max:255',
            'pages.*.translations.*.password_placeholder' => 'nullable|string|max:255',
            'pages.*.translations.*.confirm_password_label' => 'nullable|string|max:255',
            'pages.*.translations.*.confirm_password_placeholder' => 'nullable|string|max:255',
            'pages.*.translations.*.submit_label' => 'nullable|string|max:255',
            'pages.*.translations.*.loading_label' => 'nullable|string|max:255',
            'pages.*.translations.*.forgot_password_label' => 'nullable|string|max:255',
            'pages.*.translations.*.secondary_text' => 'nullable|string|max:255',
            'pages.*.translations.*.secondary_action_label' => 'nullable|string|max:255',
            'pages.*.translations.*.secondary_action_url' => 'nullable|string|max:255',
            'pages.*.translations.*.success_title' => 'nullable|string|max:255',
            'pages.*.translations.*.success_message' => 'nullable|string',
            'pages.*.translations.*.back_label' => 'nullable|string|max:255',
            'pages.*.translations.*.show_password_label' => 'nullable|string|max:255',
            'pages.*.translations.*.hide_password_label' => 'nullable|string|max:255',
            'pages.*.translations.*.validation_required_message' => 'nullable|string|max:255',
            'pages.*.translations.*.validation_mismatch_message' => 'nullable|string|max:255',
            'pages.*.translations.*.error_message' => 'nullable|string|max:255',
            'pages.*.translations.*.logo_alt' => 'nullable|string|max:255',
            'email_templates' => 'required|array',
            'email_templates.*.template_key' => 'required|string|max:100',
            'email_templates.*.is_active' => 'boolean',
            'email_templates.*.settings' => 'nullable|array',
            'email_templates.*.translations' => 'required|array',
            'email_templates.*.translations.*.subject' => 'nullable|string|max:255',
            'email_templates.*.translations.*.brand_name' => 'nullable|string|max:255',
            'email_templates.*.translations.*.greeting' => 'nullable|string|max:255',
            'email_templates.*.translations.*.intro' => 'nullable|string',
            'email_templates.*.translations.*.action_label' => 'nullable|string|max:255',
            'email_templates.*.translations.*.expiry_notice' => 'nullable|string|max:255',
            'email_templates.*.translations.*.no_action_notice' => 'nullable|string',
            'email_templates.*.translations.*.salutation' => 'nullable|string|max:255',
            'email_templates.*.translations.*.signature' => 'nullable|string|max:255',
            'email_templates.*.translations.*.subcopy' => 'nullable|string',
            'email_templates.*.translations.*.footer' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Validation failed', 422, $validator->errors()->toArray());
        }

        DB::transaction(function () use ($validator) {
            foreach ($validator->validated()['pages'] as $pageData) {
                $page = AuthPage::firstOrCreate(['page_key' => $pageData['page_key']]);
                $page->update([
                    'is_active' => $pageData['is_active'] ?? true,
                    'settings' => $pageData['settings'] ?? [],
                ]);

                foreach ($pageData['translations'] as $locale => $fields) {
                    $page->translations()->updateOrCreate(
                        ['locale' => $locale],
                        $fields + ['locale' => $locale],
                    );
                }
            }

            foreach ($validator->validated()['email_templates'] as $templateData) {
                $template = AuthEmailTemplate::firstOrCreate(['template_key' => $templateData['template_key']]);
                $template->update([
                    'is_active' => $templateData['is_active'] ?? true,
                    'settings' => $templateData['settings'] ?? [],
                ]);

                foreach ($templateData['translations'] as $locale => $fields) {
                    $template->translations()->updateOrCreate(
                        ['locale' => $locale],
                        $fields + ['locale' => $locale],
                    );
                }
            }
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());

        return $this->adminShow();
    }

    public static function localizedEmailTemplate(string $templateKey, ?string $locale = null): ?array
    {
        $controller = app(self::class);
        $locale = $locale ?: app()->getLocale();
        $fallback = $controller->fallbackLocale();

        $template = AuthEmailTemplate::with('translations')
            ->where('template_key', $templateKey)
            ->where('is_active', true)
            ->first();

        return $template ? $controller->formatEmailTemplate($template, $locale, $fallback) : null;
    }

    protected function formatPage(AuthPage $page, string $locale, string $fallback): array
    {
        $translation = $this->translation($page->translations, $locale, $fallback);

        return [
            'id' => $page->id,
            'page_key' => $page->page_key,
            'is_active' => (bool) $page->is_active,
            'settings' => $page->settings ?: [],
            'title' => $translation->title ?? '',
            'subtitle' => $translation->subtitle ?? '',
            'email_label' => $translation->email_label ?? '',
            'email_placeholder' => $translation->email_placeholder ?? '',
            'password_label' => $translation->password_label ?? '',
            'password_placeholder' => $translation->password_placeholder ?? '',
            'confirm_password_label' => $translation->confirm_password_label ?? '',
            'confirm_password_placeholder' => $translation->confirm_password_placeholder ?? '',
            'submit_label' => $translation->submit_label ?? '',
            'loading_label' => $translation->loading_label ?? '',
            'forgot_password_label' => $translation->forgot_password_label ?? '',
            'secondary_text' => $translation->secondary_text ?? '',
            'secondary_action_label' => $translation->secondary_action_label ?? '',
            'secondary_action_url' => $translation->secondary_action_url ?? '',
            'success_title' => $translation->success_title ?? '',
            'success_message' => $translation->success_message ?? '',
            'back_label' => $translation->back_label ?? '',
            'show_password_label' => $translation->show_password_label ?? '',
            'hide_password_label' => $translation->hide_password_label ?? '',
            'validation_required_message' => $translation->validation_required_message ?? '',
            'validation_mismatch_message' => $translation->validation_mismatch_message ?? '',
            'error_message' => $translation->error_message ?? '',
            'logo_alt' => $translation->logo_alt ?? '',
        ];
    }

    protected function formatEmailTemplate(AuthEmailTemplate $template, string $locale, string $fallback): array
    {
        $translation = $this->translation($template->translations, $locale, $fallback);

        return [
            'id' => $template->id,
            'template_key' => $template->template_key,
            'is_active' => (bool) $template->is_active,
            'settings' => $template->settings ?: [],
            'subject' => $translation->subject ?? '',
            'brand_name' => $translation->brand_name ?? '',
            'greeting' => $translation->greeting ?? '',
            'intro' => $translation->intro ?? '',
            'action_label' => $translation->action_label ?? '',
            'expiry_notice' => $translation->expiry_notice ?? '',
            'no_action_notice' => $translation->no_action_notice ?? '',
            'salutation' => $translation->salutation ?? '',
            'signature' => $translation->signature ?? '',
            'subcopy' => $translation->subcopy ?? '',
            'footer' => $translation->footer ?? '',
        ];
    }

    protected function translation($translations, string $locale, string $fallback)
    {
        return $translations->firstWhere('locale', $locale)
            ?: $translations->firstWhere('locale', $fallback)
            ?: $translations->first();
    }

    protected function requestLocale(Request $request): string
    {
        $locale = $request->query('locale') ?: $request->header('Accept-Language');
        $supported = $this->localeCodes();

        if ($locale) {
            $locale = strtolower(trim(explode(',', $locale)[0]));
            if (strlen($locale) > 2 && $locale[2] === '-') {
                $locale = substr($locale, 0, 2);
            }

            if (in_array($locale, $supported, true)) {
                return $locale;
            }
        }

        return $this->fallbackLocale();
    }

    protected function localeCodes(): array
    {
        $codes = Locale::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('code')
            ->filter()
            ->values()
            ->all();

        return $codes ?: ['en', 'uz', 'ru', 'ar'];
    }

    protected function fallbackLocale(): string
    {
        return $this->localeCodes()[0]
            ?? config('app.fallback_locale')
            ?? config('app.locale')
            ?? 'en';
    }
}

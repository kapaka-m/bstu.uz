<?php

namespace App\Http\Middleware;

use App\Models\Locale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (config('app.debug') && ($request->isMethod('PUT') || $request->isMethod('POST'))) {
            Log::info('Incoming request raw body content', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'parsed' => $request->all(),
                'raw' => $request->getContent(),
            ]);
        }

        $locale = $request->input('lang')
            ?? $request->query('locale')
            ?? $request->header('X-Locale')
            ?? $request->header('Accept-Language');

        if ($locale) {
            $locale = $this->normalizeRequestedLocale($locale);
            $supportedLocales = $this->supportedLocales();

            if (in_array($locale, $supportedLocales, true)) {
                App::setLocale($locale);
            } else {
                $primary = explode('-', $locale)[0] ?? '';
                if ($primary && in_array($primary, $supportedLocales, true)) {
                    App::setLocale($primary);
                }
            }
        }

        return $next($request);
    }

    protected function normalizeRequestedLocale(string $locale): string
    {
        return strtolower(trim(explode(',', str_replace('_', '-', $locale))[0]));
    }

    protected function supportedLocales(): array
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

        return $codes ?: array_filter([config('app.fallback_locale'), config('app.locale')]);
    }
}

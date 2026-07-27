<?php

namespace App\Http\Middleware;

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
        if ($request->isMethod('PUT') || $request->isMethod('POST')) {
            Log::info('Incoming request raw body content', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'parsed' => $request->all(),
                'raw' => $request->getContent(),
            ]);
        }

        $locale = $request->input('lang') ?? $request->header('Accept-Language');

        if ($locale) {
            $locale = explode(',', $locale)[0];
            $locale = strtolower(trim($locale));

            if (strlen($locale) > 2 && $locale[2] === '-') {
                $locale = substr($locale, 0, 2);
            }

            $supportedLocales = ['en', 'uz', 'ru', 'ar'];

            if (in_array($locale, $supportedLocales)) {
                App::setLocale($locale);
            }
        }

        return $next($request);
    }
}

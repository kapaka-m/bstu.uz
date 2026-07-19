<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
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

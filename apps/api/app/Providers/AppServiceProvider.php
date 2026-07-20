<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('auth', function (Request $request) {
            $email = strtolower((string) $request->input('email'));
            $key = trim($email) !== '' ? $email.'|'.$request->ip() : $request->ip();

            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('auth-register', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('password-reset', function (Request $request) {
            $email = strtolower((string) $request->input('email'));
            $key = trim($email) !== '' ? $email.'|'.$request->ip() : $request->ip();

            return Limit::perMinute(3)->by($key);
        });

        RateLimiter::for('public-api', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip());
        });

        RateLimiter::for('student-api', function (Request $request) {
            return Limit::perMinute(90)->by(optional($request->user())->id ?: $request->ip());
        });

        RateLimiter::for('apanel-api', function (Request $request) {
            return Limit::perMinute(600)->by(optional($request->user())->id ?: $request->ip());
        });

        RateLimiter::for('uploads', function (Request $request) {
            return Limit::perMinute(20)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}

<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
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
        ResetPassword::createUrlUsing(function ($notifiable, string $token): string {
            $frontendUrl = rtrim((string) config('app.frontend_url'), '/');

            return $frontendUrl.'/reset-password?'.http_build_query([
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ]);
        });

        RateLimiter::for('api', function (Request $request) {
            return app()->environment('local')
                ? Limit::none()
                : Limit::perMinute(600)->by($request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            if (app()->environment('local')) {
                return Limit::none();
            }
            $email = strtolower((string) $request->input('email'));
            $key = trim($email) !== '' ? $email.'|'.$request->ip() : $request->ip();

            return Limit::perMinute(5)->by($key);
        });

        RateLimiter::for('auth-register', function (Request $request) {
            return app()->environment('local')
                ? Limit::none()
                : Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('password-reset', function (Request $request) {
            if (app()->environment('local')) {
                return Limit::none();
            }
            $email = strtolower((string) $request->input('email'));
            $key = trim($email) !== '' ? $email.'|'.$request->ip() : $request->ip();

            return Limit::perMinute(3)->by($key);
        });

        RateLimiter::for('public-api', function (Request $request) {
            return app()->environment('local')
                ? Limit::none()
                : Limit::perMinute(600)->by($request->ip());
        });

        RateLimiter::for('student-api', function (Request $request) {
            return app()->environment('local')
                ? Limit::none()
                : Limit::perMinute(90)->by(optional($request->user())->id ?: $request->ip());
        });

        RateLimiter::for('apanel-api', function (Request $request) {
            return app()->environment('local')
                ? Limit::none()
                : Limit::perMinute(600)->by(optional($request->user())->id ?: $request->ip());
        });

        RateLimiter::for('uploads', function (Request $request) {
            return app()->environment('local')
                ? Limit::none()
                : Limit::perMinute(20)->by(optional($request->user())->id ?: $request->ip());
        });
    }
}

<?php

namespace App\Mail;

use App\Http\Controllers\Api\AuthCmsController;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CmsPasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $token,
        public ?string $locale = null,
    ) {
    }

    public function build(): self
    {
        $locale = $this->locale ?: app()->getLocale();
        $template = AuthCmsController::localizedEmailTemplate('password_reset', $locale)
            ?: $this->fallbackTemplate();

        $settings = $template['settings'] ?? [];
        $resetUrl = $this->resetUrl($settings);
        $minutes = (string) ($settings['expire_minutes'] ?? config('auth.passwords.users.expire', 60));
        $year = now()->format('Y');
        $actionLabel = $template['action_label'] ?: 'Reset Password';

        $data = [
            'template' => $this->replaceTokens($template, $minutes, $year, $actionLabel),
            'settings' => [
                'brand_url' => $settings['brand_url'] ?? config('app.url'),
                'button_color' => $settings['button_color'] ?? '#18181b',
            ],
            'resetUrl' => $resetUrl,
            'direction' => $locale === 'ar' ? 'rtl' : 'ltr',
        ];

        return $this
            ->subject($template['subject'] ?: 'Reset your password')
            ->view('emails.auth.password-reset', $data)
            ->text('emails.auth.password-reset-text', $data);
    }

    protected function resetUrl(array $settings): string
    {
        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');
        $path = '/'.ltrim((string) ($settings['frontend_reset_path'] ?? '/reset-password'), '/');

        return $frontendUrl.$path.'?'.http_build_query([
            'token' => $this->token,
            'email' => $this->user->getEmailForPasswordReset(),
        ]);
    }

    protected function replaceTokens(array $template, string $minutes, string $year, string $actionLabel): array
    {
        foreach ($template as $key => $value) {
            if (! is_string($value)) {
                continue;
            }

            $template[$key] = str_replace(
                ['{minutes}', '{year}', '{action_label}'],
                [$minutes, $year, $actionLabel],
                $value,
            );
        }

        return $template;
    }

    protected function fallbackTemplate(): array
    {
        return [
            'settings' => [
                'brand_url' => config('app.url'),
                'button_color' => '#18181b',
                'frontend_reset_path' => '/reset-password',
                'expire_minutes' => config('auth.passwords.users.expire', 60),
            ],
            'subject' => 'Reset your password',
            'brand_name' => config('app.name'),
            'greeting' => 'Hello!',
            'intro' => 'You are receiving this email because we received a password reset request for your account.',
            'action_label' => 'Reset Password',
            'expiry_notice' => 'This password reset link will expire in {minutes} minutes.',
            'no_action_notice' => 'If you did not request a password reset, no further action is required.',
            'salutation' => 'Regards,',
            'signature' => config('app.name'),
            'subcopy' => 'If you are having trouble clicking the "{action_label}" button, copy and paste the URL below into your web browser:',
            'footer' => '© {year} '.config('app.name').'. All rights reserved.',
        ];
    }
}

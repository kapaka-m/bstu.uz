<?php

namespace App\Mail;

use App\Http\Controllers\Api\AuthCmsController;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CmsTemplateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $templateKey,
        public array $tokens = [],
        public ?string $mailLocale = null,
        public ?string $subjectOverride = null,
    ) {}

    public function build(): self
    {
        $locale = $this->mailLocale ?: app()->getLocale();
        $template = AuthCmsController::localizedEmailTemplate($this->templateKey, $locale)
            ?: $this->fallbackTemplate();

        $settings = $template['settings'] ?? [];
        $tokens = array_merge([
            'year' => now()->format('Y'),
            'action_label' => $template['action_label'] ?? '',
        ], $this->tokens);

        $template = $this->replaceTokens($template, $tokens);

        $data = [
            'template' => $template,
            'settings' => [
                'brand_url' => $settings['brand_url'] ?? config('app.url'),
                'button_color' => $settings['button_color'] ?? '#0d6efd',
                'accent_color' => $settings['accent_color'] ?? '#eef4ff',
            ],
            'ctaUrl' => $tokens['cta_url'] ?? null,
            'direction' => $locale === 'ar' ? 'rtl' : 'ltr',
        ];

        return $this
            ->subject($this->subjectOverride ?: ($template['subject'] ?: config('app.name')))
            ->view('emails.cms.template', $data)
            ->text('emails.cms.template-text', $data);
    }

    protected function replaceTokens(array $template, array $tokens): array
    {
        $search = array_map(fn ($key) => '{'.$key.'}', array_keys($tokens));
        $replace = array_values($tokens);

        foreach ($template as $key => $value) {
            if (is_string($value)) {
                $template[$key] = str_replace($search, $replace, $value);
            }
        }

        return $template;
    }

    protected function fallbackTemplate(): array
    {
        return [
            'settings' => [
                'brand_url' => config('app.url'),
                'button_color' => '#0d6efd',
            ],
            'subject' => config('app.name'),
            'brand_name' => config('app.name'),
            'greeting' => 'Hello!',
            'intro' => '',
            'action_label' => '',
            'expiry_notice' => '',
            'no_action_notice' => '',
            'salutation' => 'Regards,',
            'signature' => config('app.name'),
            'subcopy' => '',
            'footer' => '© {year} '.config('app.name').'. All rights reserved.',
        ];
    }
}

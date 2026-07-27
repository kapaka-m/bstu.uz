<?php

namespace App\Services;

use App\Models\Setting;

class CmsSettingService
{
    public function text(string $key, ?string $default = null): ?string
    {
        $value = $this->value($key, $default);

        return $value === null ? null : (string) $value;
    }

    public function int(string $key, int $default = 0): int
    {
        return (int) $this->value($key, $default);
    }

    public function float(string $key, float $default = 0.0): float
    {
        return (float) $this->value($key, $default);
    }

    public function list(string $key, array $default = []): array
    {
        $value = $this->value($key, $default);
        if (is_array($value)) {
            return array_values(array_filter($value, fn ($item) => $item !== null && $item !== ''));
        }

        return collect(preg_split('/\r\n|\r|\n|,/', (string) $value) ?: [])
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }

    public function render(string $key, array $replacements = [], ?string $default = null): string
    {
        $template = $this->text($key, $default) ?? '';
        foreach ($replacements as $name => $value) {
            $template = str_replace('{{'.$name.'}}', (string) $value, $template);
        }

        return $template;
    }

    public function value(string $key, mixed $default = null): mixed
    {
        $setting = Setting::query()->where('key', $key)->first();
        if (! $setting) {
            return $default;
        }

        return match ($setting->type) {
            'number', 'integer' => is_numeric($setting->value) ? (int) $setting->value : $default,
            'decimal', 'float' => is_numeric($setting->value) ? (float) $setting->value : $default,
            'boolean', 'bool' => filter_var($setting->value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? (bool) $default,
            'json', 'array' => json_decode((string) $setting->value, true) ?: $default,
            default => $setting->value ?? $default,
        };
    }
}

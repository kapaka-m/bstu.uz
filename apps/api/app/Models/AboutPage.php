<?php

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AboutPage extends Model
{
    use HasTranslations;

    protected $fillable = [
        'key',
        'hero_contact_url',
        'hero_campus_url',
        'identity_image',
        'rector_profile_slug',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function contentEntries(): HasMany
    {
        return $this->hasMany(AboutPageContentEntry::class);
    }

    public function contentForLocale(string $locale): array
    {
        $entries = $this->contentEntries()
            ->with('translations')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $content = [];

        foreach ($entries as $entry) {
            $translation = $entry->translations
                ->first(fn ($item) => $item->locale === $locale && $item->value !== null && $item->value !== '')
                ?: $entry->translations
                    ->first(fn ($item) => $item->locale === 'en' && $item->value !== null && $item->value !== '')
                ?: $entry->translations
                    ->first(fn ($item) => $item->value !== null && $item->value !== '');

            if (! $translation) {
                continue;
            }

            self::setContentPath($content, $entry->path, self::castContentValue($translation->value, $entry->value_type));
        }

        return self::normalizeContentArrays($content);
    }

    public function cmsTranslationsPayload(array $locales = ['en', 'uz', 'ru', 'ar']): array
    {
        return collect($locales)->map(fn (string $locale) => [
            'locale' => $locale,
            'content' => $this->contentForLocale($locale),
        ])->all();
    }

    public function syncTranslationsMirror(array $locales = ['en', 'uz', 'ru', 'ar']): void
    {
        foreach ($this->cmsTranslationsPayload($locales) as $translation) {
            $this->translations()->updateOrCreate(
                ['locale' => $translation['locale']],
                ['content' => $translation['content']]
            );
        }
    }

    public function replaceContentTranslations(array $translations): void
    {
        $knownPaths = [];
        $sortOrder = 0;

        foreach ($translations as $locale => $fields) {
            $content = $fields['content'] ?? [];
            if (! is_array($content)) {
                continue;
            }

            foreach (self::flattenContent($content) as $path => $value) {
                $knownPaths[$path] = true;
                $entry = $this->contentEntries()->firstOrCreate(
                    ['path' => $path],
                    [
                        'value_type' => self::detectContentType($value),
                        'sort_order' => $sortOrder,
                        'is_active' => true,
                    ]
                );

                $entry->update([
                    'value_type' => self::detectContentType($value),
                    'sort_order' => $entry->sort_order ?? $sortOrder,
                    'is_active' => true,
                ]);

                $entry->translations()->updateOrCreate(
                    ['locale' => $locale],
                    ['value' => self::contentValueToStorage($value)]
                );

                $sortOrder++;
            }
        }

        if ($knownPaths) {
            $this->contentEntries()
                ->whereNotIn('path', array_keys($knownPaths))
                ->delete();
        }

        $this->syncTranslationsMirror(array_keys($translations));
    }

    protected static function flattenContent(array $content, string $prefix = ''): array
    {
        $flat = [];

        foreach ($content as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            if (is_array($value) && $value !== []) {
                $flat += self::flattenContent($value, $path);
                continue;
            }

            if (is_array($value)) {
                continue;
            }

            $flat[$path] = $value;
        }

        return $flat;
    }

    protected static function setContentPath(array &$content, string $path, mixed $value): void
    {
        $segments = explode('.', $path);
        $target =& $content;

        foreach ($segments as $index => $segment) {
            $isLast = $index === count($segments) - 1;
            $key = ctype_digit($segment) ? (int) $segment : $segment;

            if ($isLast) {
                $target[$key] = $value;
                break;
            }

            if (! isset($target[$key]) || ! is_array($target[$key])) {
                $target[$key] = [];
            }

            $target =& $target[$key];
        }
    }

    protected static function normalizeContentArrays(array $content): array
    {
        foreach ($content as $key => $value) {
            if (is_array($value)) {
                $content[$key] = self::normalizeContentArrays($value);
            }
        }

        $keys = array_keys($content);
        $numericKeys = array_filter($keys, fn ($key) => is_int($key) || ctype_digit((string) $key));

        if ($keys !== [] && count($numericKeys) === count($keys)) {
            ksort($content, SORT_NUMERIC);

            return array_values($content);
        }

        return $content;
    }

    protected static function detectContentType(mixed $value): string
    {
        if (is_bool($value)) {
            return 'boolean';
        }

        if (is_int($value) || is_float($value)) {
            return 'number';
        }

        return 'text';
    }

    protected static function castContentValue(?string $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'number' => is_numeric($value) ? $value + 0 : $value,
            default => $value,
        };
    }

    protected static function contentValueToStorage(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }
}

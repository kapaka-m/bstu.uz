<?php

namespace Database\Seeders\Concerns;

use App\Models\Locale;

trait ResolvesSeedLocales
{
    private function activeSeedLocales(): array
    {
        $locales = Locale::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('code')
            ->all();

        return $locales;
    }

    private function sourceSeedLocale(array $translations, array $locales): ?string
    {
        foreach ($locales as $locale) {
            if (isset($translations[$locale]) && is_array($translations[$locale])) {
                return $locale;
            }
        }

        foreach ($translations as $locale => $localeData) {
            if (is_array($localeData)) {
                return $locale;
            }
        }

        return null;
    }
}

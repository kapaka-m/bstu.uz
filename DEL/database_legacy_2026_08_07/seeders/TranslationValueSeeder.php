<?php

namespace Database\Seeders;

use App\Models\Locale;
use App\Models\TranslationKey;
use App\Models\TranslationValue;
use Illuminate\Database\Seeder;

class TranslationValueSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('data/translations.json');
        if (! file_exists($filePath)) {
            $this->command->error('translations.json not found!');

            return;
        }

        $translations = json_decode(file_get_contents($filePath), true);

        $locales = Locale::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('code')
            ->all();

        if ($locales === []) {
            $locales = array_values(array_filter(
                array_keys($translations),
                fn (string $key) => is_array($translations[$key] ?? null)
            ));
        }

        $flatByLocale = [];

        foreach ($locales as $locale) {
            $langData = $translations[$locale] ?? [];
            $flatByLocale[$locale] = $this->flattenArray($langData);
        }

        // Get all translation keys
        $allKeys = TranslationKey::all();

        foreach ($allKeys as $keyModel) {
            $group = $keyModel->group;
            $k = $keyModel->key;

            foreach ($locales as $locale) {
                // Reconstruct the flat key inside original translations
                $possibleFlatKeys = [
                    "{$group}.{$k}",
                    "common.{$k}",
                ];

                if ($group === 'button') {
                    $possibleFlatKeys[] = "common.{$k}";
                    $possibleFlatKeys[] = 'common.'.str_replace('button.', '', $k);
                    $possibleFlatKeys[] = "button.{$k}";
                }
                if ($group === 'form') {
                    $possibleFlatKeys[] = "common.{$k}";
                }

                $value = null;
                foreach ($possibleFlatKeys as $pfk) {
                    if (isset($flatByLocale[$locale][$pfk])) {
                        $value = $flatByLocale[$locale][$pfk];
                        break;
                    }
                }

                if (is_null($value)) {
                    continue;
                }

                TranslationValue::firstOrCreate([
                    'translation_key_id' => $keyModel->id,
                    'locale' => $locale,
                ], [
                    'value' => $value,
                ]);
            }
        }
    }

    private function flattenArray(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $prefix.$key.'.'));
            } else {
                $result[$prefix.$key] = $value;
            }
        }

        return $result;
    }
}

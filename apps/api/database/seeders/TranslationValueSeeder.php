<?php

namespace Database\Seeders;

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

        // Group mapping mapping
        $groupMapping = [
            'nav' => 'nav',
            'common' => 'common',
            'about' => 'about',
            'home' => 'home',
            'auth' => 'auth',
            'validation' => 'validation',
            'status' => 'status',
            'student' => 'student',
            'apanel' => 'apanel',
            'application' => 'application',
            'notification' => 'notification',
            'menu' => 'menu',
            'error' => 'error',
            'success' => 'success',
            'button' => 'button',
            'form' => 'form',
            'section' => 'section',
            'breadcrumb' => 'breadcrumb',
            'page' => 'page',
        ];

        // Seed values from translations.json
        $locales = ['en', 'uz', 'ru', 'ar'];
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

                // If not found in locale, check English version for fallback
                if (is_null($value) && $locale !== 'en') {
                    foreach ($possibleFlatKeys as $pfk) {
                        if (isset($flatByLocale['en'][$pfk])) {
                            $value = $flatByLocale['en'][$pfk];
                            break;
                        }
                    }
                }

                // Default fallback to key if still null
                if (is_null($value)) {
                    // Check if it's one of the extra keys:
                    $value = $k;
                }

                TranslationValue::updateOrCreate([
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

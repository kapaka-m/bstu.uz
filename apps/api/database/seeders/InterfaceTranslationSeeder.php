<?php

namespace Database\Seeders;

use App\Models\TranslationKey;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InterfaceTranslationSeeder extends Seeder
{
    public function run(): void
    {
        $entries = json_decode(file_get_contents(database_path('data/interface_translations.json')), true, flags: JSON_THROW_ON_ERROR);
        DB::transaction(function () use ($entries) {
            foreach ($entries as $name => $values) {
                $key = TranslationKey::firstOrCreate(
                    ['group' => 'interface', 'key' => $name],
                    ['description' => 'Interface text: '.$values[0], 'is_system' => true],
                );
                foreach (['en', 'uz', 'ru', 'ar'] as $index => $locale) {
                    $key->values()->firstOrCreate(['locale' => $locale], ['value' => $values[$index]]);
                }
            }
        });
        Cache::forever('public_content_cache_version', (string) Str::uuid());
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $keyId = DB::table('translation_keys')->updateOrInsert(
            ['group' => 'apanel', 'key' => 'crud.ui.label.additionalDepartments'],
            [
                'description' => 'Apanel staff form label for secondary department links',
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

        $keyId = DB::table('translation_keys')
            ->where('group', 'apanel')
            ->where('key', 'crud.ui.label.additionalDepartments')
            ->value('id');

        if (! $keyId) {
            return;
        }

        foreach ($this->values() as $locale => $value) {
            DB::table('translation_values')->updateOrInsert(
                ['translation_key_id' => $keyId, 'locale' => $locale],
                [
                    'value' => $value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );
        }

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        $keyId = DB::table('translation_keys')
            ->where('group', 'apanel')
            ->where('key', 'crud.ui.label.additionalDepartments')
            ->value('id');

        if ($keyId) {
            DB::table('translation_values')->where('translation_key_id', $keyId)->delete();
            DB::table('translation_keys')->where('id', $keyId)->delete();
        }
    }

    private function values(): array
    {
        return [
            'en' => 'Additional Departments',
            'uz' => 'Qo‘shimcha kafedralar',
            'ru' => 'Дополнительные кафедры',
            'ar' => 'الأقسام الإضافية',
        ];
    }
};

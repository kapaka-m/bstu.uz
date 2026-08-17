<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('translation_keys') || ! Schema::hasTable('translation_values')) {
            return;
        }

        $legacyKeys = [
            ['nav', 'contact'],
            ['common', 'contactUs'],
            ['common', 'contactRegistrarDesc'],
            ['common', 'contactInfo'],
        ];

        $keyIds = DB::table('translation_keys')
            ->where(function ($query) use ($legacyKeys) {
                foreach ($legacyKeys as [$group, $key]) {
                    $query->orWhere(function ($nested) use ($group, $key) {
                        $nested->where('group', $group)->where('key', $key);
                    });
                }
            })
            ->pluck('id');

        if ($keyIds->isEmpty()) {
            return;
        }

        DB::table('translation_values')
            ->whereIn('translation_key_id', $keyIds)
            ->delete();

        DB::table('translation_keys')
            ->whereIn('id', $keyIds)
            ->delete();

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        // Removed unused legacy labels are intentionally not recreated.
    }
};

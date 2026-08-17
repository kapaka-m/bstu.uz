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

        $legacyPrefixes = [
            'rector.',
            'vice-rector-youth.',
            'vice-rector-research.',
            'vice-rector-academic.',
            'vice-rector-finance.',
            'vice-rector-international.',
        ];

        $keyIds = DB::table('translation_keys')
            ->where('group', 'common')
            ->where(function ($query) use ($legacyPrefixes) {
                foreach ($legacyPrefixes as $prefix) {
                    $query->orWhere('key', 'like', $prefix.'%');
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
        // Administration profile content is stored in administration_profile_* tables.
        // Removed legacy translation keys are intentionally not recreated.
    }
};

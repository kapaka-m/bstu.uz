<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $ids = DB::table('translation_keys')
            ->where('group', 'home')
            ->get(['id', 'key'])
            ->filter(fn ($row) => $this->shouldRemove($row->key))
            ->pluck('id')
            ->values();

        if ($ids->isEmpty()) {
            return;
        }

        DB::table('translation_values')
            ->whereIn('translation_key_id', $ids)
            ->delete();

        DB::table('translation_keys')
            ->whereIn('id', $ids)
            ->delete();

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function shouldRemove(string $key): bool
    {
        $keptProgramKeys = [
            'programs.tag',
            'programs.title',
            'programs.explore',
            'programs.viewAll',
        ];

        if (in_array($key, $keptProgramKeys, true)) {
            return false;
        }

        if (str_starts_with($key, 'programs.degrees.') || str_starts_with($key, 'programs.durations.')) {
            return false;
        }

        return true;
    }
};

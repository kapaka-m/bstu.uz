<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $keyIds = DB::table('translation_keys')
            ->where('group', 'about')
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

        DB::table('settings')->updateOrInsert(
            ['key' => 'public_content_cache_version'],
            ['value' => (string) now()->timestamp, 'updated_at' => now(), 'created_at' => now()]
        );
    }

    public function down(): void
    {
        // Legacy About content belongs to the About CMS tables and is not restored to translation keys.
    }
};

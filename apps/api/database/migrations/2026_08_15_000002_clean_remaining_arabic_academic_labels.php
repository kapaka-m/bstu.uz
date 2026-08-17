<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['faculty_translations', 'department_translations'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'content_sections')) {
                continue;
            }

            DB::table($table)
                ->where('locale', 'ar')
                ->where('content_sections', 'like', '%Faculty member%')
                ->orderBy('id')
                ->select(['id', 'content_sections'])
                ->chunkById(100, function ($rows) use ($table) {
                    foreach ($rows as $row) {
                        DB::table($table)->where('id', $row->id)->update([
                            'content_sections' => str_replace('Faculty member', 'عضو هيئة تدريس', (string) $row->content_sections),
                            'updated_at' => now(),
                        ]);
                    }
                });
        }

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        // Intentionally not reversible: this migration fixes localized Arabic labels.
    }
};

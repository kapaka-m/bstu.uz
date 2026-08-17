<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('staff_profiles') || ! Schema::hasTable('staff_profile_translations')) {
            return;
        }

        $staffIds = DB::table('staff_profiles')
            ->where('is_active', false)
            ->pluck('id')
            ->all();

        if ($staffIds === []) {
            return;
        }

        DB::table('staff_profile_translations')->whereIn('staff_profile_id', $staffIds)->delete();
        DB::table('staff_profiles')->whereIn('id', $staffIds)->delete();

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }
};

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

        $unusedKeys = [
            ['group' => 'facultyTechnology', 'key' => 'academicPathways'],
            ['group' => 'facultyTechnology', 'key' => 'bachelorDesc'],
            ['group' => 'facultyTechnology', 'key' => 'contactDesc'],
            ['group' => 'facultyTechnology', 'key' => 'deanContact'],
            ['group' => 'facultyTechnology', 'key' => 'departmentsDesc'],
            ['group' => 'facultyTechnology', 'key' => 'deputyDeanContacts'],
            ['group' => 'facultyTechnology', 'key' => 'industryCooperation'],
            ['group' => 'facultyTechnology', 'key' => 'leadershipDesc'],
            ['group' => 'facultyTechnology', 'key' => 'masterDesc'],
            ['group' => 'facultyTechnology', 'key' => 'masterSpecializations'],
            ['group' => 'facultyTechnology', 'key' => 'quickDepartmentLinks'],
        ];

        $ids = DB::table('translation_keys')
            ->where(function ($query) use ($unusedKeys) {
                foreach ($unusedKeys as $item) {
                    $query->orWhere(function ($inner) use ($item) {
                        $inner
                            ->where('group', $item['group'])
                            ->where('key', $item['key']);
                    });
                }
            })
            ->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        DB::table('translation_values')->whereIn('translation_key_id', $ids)->delete();
        DB::table('translation_keys')->whereIn('id', $ids)->delete();
        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }
};

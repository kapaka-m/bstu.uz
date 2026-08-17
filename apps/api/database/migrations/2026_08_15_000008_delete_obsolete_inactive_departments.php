<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $obsoleteDepartmentSlugs = [
        'artificial-intelligence-digitalization',
        'economics-management',
        'information-communication-technologies',
        'textile-materials-science',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('departments')) {
            return;
        }

        $departmentIds = DB::table('departments')
            ->whereIn('slug', $this->obsoleteDepartmentSlugs)
            ->pluck('id')
            ->all();

        if ($departmentIds === []) {
            return;
        }

        if (Schema::hasTable('applications') && Schema::hasColumn('applications', 'department_id')) {
            DB::table('applications')
                ->whereIn('department_id', $departmentIds)
                ->update(['department_id' => null, 'updated_at' => now()]);
        }

        if (Schema::hasTable('programs') && Schema::hasColumn('programs', 'department_id')) {
            DB::table('programs')
                ->whereIn('department_id', $departmentIds)
                ->update(['department_id' => null, 'updated_at' => now()]);
        }

        $staffIds = Schema::hasTable('staff_profiles')
            ? DB::table('staff_profiles')->whereIn('department_id', $departmentIds)->pluck('id')->all()
            : [];

        if ($staffIds !== []) {
            if (Schema::hasTable('staff_profile_translations')) {
                DB::table('staff_profile_translations')->whereIn('staff_profile_id', $staffIds)->delete();
            }

            DB::table('staff_profiles')->whereIn('id', $staffIds)->delete();
        }

        if (Schema::hasTable('department_translations')) {
            DB::table('department_translations')->whereIn('department_id', $departmentIds)->delete();
        }

        if (Schema::hasTable('menu_items')) {
            $urls = array_map(
                fn (string $slug) => '/department/'.$slug,
                $this->obsoleteDepartmentSlugs,
            );
            $menuItemIds = DB::table('menu_items')->whereIn('url', $urls)->pluck('id')->all();

            if ($menuItemIds !== []) {
                if (Schema::hasTable('menu_item_translations')) {
                    DB::table('menu_item_translations')->whereIn('menu_item_id', $menuItemIds)->delete();
                }

                DB::table('menu_items')->whereIn('id', $menuItemIds)->delete();
            }
        }

        DB::table('departments')->whereIn('id', $departmentIds)->delete();
        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }
};

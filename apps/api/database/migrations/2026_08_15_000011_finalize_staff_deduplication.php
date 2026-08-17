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

        DB::transaction(function () {
            $this->deleteDuplicateWhenCanonicalExists(
                'agricultural-products-storage-oil-fat-technology-bozorov-dilmurod-kholmurodovich',
                'faculty-of-technology-youth-bozorov-dilmurod-kholmurodovich'
            );

            $this->deleteDuplicateWhenCanonicalExists(
                'light-industry-engineering-and-design-3-aziza-s-jamilova',
                'uzbek-foreign-languages-2-aziza-s-jamilova'
            );

            foreach ([
                'oil-gas-engineering-upstream-downstream-rakhimov-bobomurod-rustamovich-assistant-phd' => 'oil-gas-engineering-upstream-downstream-rakhimov-bobomurod-rustamovich',
                'oil-gas-engineering-upstream-downstream-obidov-hamid-olimovich-senior-lecturer-sattorov' => 'oil-gas-engineering-upstream-downstream-obidov-hamid-olimovich',
                'oil-gas-engineering-upstream-downstream-mirvohid-olimovich-senior-lecturer' => 'oil-gas-engineering-upstream-downstream-sattorov-mirvohid-olimovich',
                'oil-gas-engineering-upstream-downstream-yamaletdinova-aygul-akhmadovna-assistant' => 'oil-gas-engineering-upstream-downstream-yamaletdinova-aygul-akhmadovna',
                'oil-gas-engineering-upstream-downstream-bokieva-shakhnoza-komilovna-assistant' => 'oil-gas-engineering-upstream-downstream-bokieva-shakhnoza-komilovna',
            ] as $duplicateSlug => $canonicalSlug) {
                $this->deleteDuplicateWhenCanonicalExists($duplicateSlug, $canonicalSlug);
            }
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function deleteDuplicateWhenCanonicalExists(string $duplicateSlug, string $canonicalSlug): void
    {
        $canonicalId = DB::table('staff_profiles')->where('slug', $canonicalSlug)->value('id');
        $duplicateId = DB::table('staff_profiles')->where('slug', $duplicateSlug)->value('id');

        if (! $canonicalId || ! $duplicateId || (int) $canonicalId === (int) $duplicateId) {
            return;
        }

        DB::table('staff_profile_translations')->where('staff_profile_id', $duplicateId)->delete();
        DB::table('staff_profiles')->where('id', $duplicateId)->delete();
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('staff_profile_department')) {
            Schema::create('staff_profile_department', function (Blueprint $table) {
                $table->id();
                $table->foreignId('staff_profile_id')->constrained('staff_profiles')->cascadeOnDelete();
                $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['staff_profile_id', 'department_id'], 'staff_department_unique');
                $table->index('department_id');
            });
        }

        DB::transaction(function () {
            $electricalId = DB::table('departments')->where('slug', 'electrical-power-engineering')->value('id');
            $architectureId = DB::table('departments')->where('slug', 'architecture')->value('id');

            if (! $electricalId || ! $architectureId) {
                return;
            }

            $canonical = DB::table('staff_profiles')
                ->where('slug', 'electrical-power-engineering-vakhitov-mubin-muminovich')
                ->first();

            $duplicate = DB::table('staff_profiles')
                ->where('slug', 'architecture-vakhitov-mubin-muminovich')
                ->first();

            if (! $canonical) {
                return;
            }

            DB::table('staff_profile_department')->updateOrInsert(
                [
                    'staff_profile_id' => $canonical->id,
                    'department_id' => $architectureId,
                ],
                [
                    'sort_order' => (int) ($duplicate->sort_order ?? $canonical->sort_order ?? 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            );

            if ($duplicate && (int) $duplicate->id !== (int) $canonical->id) {
                $updates = [];
                foreach (['photo', 'email', 'phone'] as $field) {
                    if (empty($canonical->{$field}) && ! empty($duplicate->{$field})) {
                        $updates[$field] = $duplicate->{$field};
                    }
                }

                if ($updates) {
                    $updates['updated_at'] = now();
                    DB::table('staff_profiles')->where('id', $canonical->id)->update($updates);
                }

                DB::table('staff_profile_translations')->where('staff_profile_id', $duplicate->id)->delete();
                DB::table('staff_profiles')->where('id', $duplicate->id)->delete();
            }
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_profile_department');
    }
};

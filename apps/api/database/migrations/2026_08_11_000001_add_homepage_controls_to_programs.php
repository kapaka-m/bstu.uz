<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            if (! Schema::hasColumn('programs', 'show_on_homepage')) {
                $table->boolean('show_on_homepage')->default(false)->after('is_active');
            }

            if (! Schema::hasColumn('programs', 'homepage_sort_order')) {
                $table->unsignedInteger('homepage_sort_order')->default(0)->after('show_on_homepage');
            }
        });

        $featuredSlugs = [
            'economics-60410100',
            'design-footwear-and-accessories-design-60210400',
            'ecology-and-environmental-protection-60520200',
            'hydrology-60530400',
            'chemical-engineering-60710100',
            'hydropower-engineering-60710600',
        ];

        foreach ($featuredSlugs as $index => $slug) {
            DB::table('programs')
                ->where('slug', $slug)
                ->update([
                    'show_on_homepage' => true,
                    'homepage_sort_order' => $index + 1,
                    'updated_at' => now(),
                ]);
        }

        if (DB::table('programs')->where('show_on_homepage', true)->count() === 0) {
            DB::table('programs')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->limit(6)
                ->get(['id'])
                ->each(function ($program, int $index) {
                    DB::table('programs')
                        ->where('id', $program->id)
                        ->update([
                            'show_on_homepage' => true,
                            'homepage_sort_order' => $index + 1,
                            'updated_at' => now(),
                        ]);
                });
        }
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            if (Schema::hasColumn('programs', 'homepage_sort_order')) {
                $table->dropColumn('homepage_sort_order');
            }

            if (Schema::hasColumn('programs', 'show_on_homepage')) {
                $table->dropColumn('show_on_homepage');
            }
        });
    }
};

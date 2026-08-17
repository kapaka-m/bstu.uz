<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $programs = [
            '60710100' => [
                'name' => 'Chemical Engineering',
                'slug' => 'chemical-engineering-60710100',
            ],
            '60710200' => [
                'name' => 'Biotechnology',
                'slug' => 'biotechnology-60710200',
            ],
            '60710800' => [
                'name' => 'Metrology and Standardization',
                'slug' => 'metrology-and-standardization-60710800',
            ],
            '60720100' => [
                'name' => 'Food Technology',
                'slug' => 'food-technology-60720100',
            ],
            '60720600' => [
                'name' => 'Oil and Oil-Gas Processing Technology',
                'slug' => 'oil-and-oil-gas-processing-technology-60720600',
            ],
            '60721100' => [
                'name' => 'Oil and Gas Engineering',
                'slug' => 'oil-and-gas-engineering-60721100',
            ],
            '60730600' => [
                'name' => 'Hydraulic and Geotechnical Engineering',
                'slug' => 'hydraulic-and-geotechnical-engineering-60730600',
            ],
        ];

        foreach ($programs as $code => $program) {
            $ids = DB::table('programs as p')
                ->join('program_translations as pt', 'pt.program_id', '=', 'p.id')
                ->where(function ($query) use ($code) {
                    $query->where('p.code', $code)->orWhere('p.official_code', $code);
                })
                ->where('pt.locale', 'en')
                ->where('pt.name', $program['name'])
                ->pluck('p.id');

            if ($ids->isEmpty()) {
                continue;
            }

            $canonicalId = DB::table('programs')
                ->whereIn('id', $ids)
                ->where('slug', $program['slug'])
                ->value('id');

            if (! $canonicalId) {
                continue;
            }

            DB::table('programs')
                ->whereIn('id', $ids)
                ->update([
                    'is_active' => false,
                    'updated_at' => now(),
                ]);

            DB::table('programs')
                ->where('id', $canonicalId)
                ->update([
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
        }

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        // Normalization prevents duplicate active public programs for the official PDF codes.
    }
};

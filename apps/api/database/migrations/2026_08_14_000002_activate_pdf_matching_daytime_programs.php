<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $programs = [
            '60710100' => 'Chemical Engineering',
            '60710200' => 'Biotechnology',
            '60710800' => 'Metrology and Standardization',
            '60720100' => 'Food Technology',
            '60720600' => 'Oil and Oil-Gas Processing Technology',
            '60721100' => 'Oil and Gas Engineering',
            '60730600' => 'Hydraulic and Geotechnical Engineering',
        ];

        foreach ($programs as $code => $name) {
            $ids = DB::table('programs as p')
                ->join('program_translations as pt', 'pt.program_id', '=', 'p.id')
                ->where(function ($query) use ($code) {
                    $query->where('p.code', $code)->orWhere('p.official_code', $code);
                })
                ->where('pt.locale', 'en')
                ->where('pt.name', $name)
                ->pluck('p.id');

            if ($ids->isEmpty()) {
                continue;
            }

            DB::table('programs')
                ->whereIn('id', $ids)
                ->update([
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
        }

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        // This sync reflects the official 2026/2027 daytime admission quota PDF.
    }
};

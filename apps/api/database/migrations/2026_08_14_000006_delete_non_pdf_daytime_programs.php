<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('programs') || ! Schema::hasTable('program_translations')) {
            return;
        }

        $keepIds = [];

        foreach ($this->pdfPrograms() as [$code, $name]) {
            $matches = DB::table('programs as p')
                ->join('program_translations as pt', 'pt.program_id', '=', 'p.id')
                ->where('pt.locale', 'en')
                ->where('p.is_active', true)
                ->where('p.official_code', $code)
                ->orderBy('p.id')
                ->get(['p.id', 'pt.name']);

            $match = $matches->first(fn ($row) => $this->normalize($row->name) === $this->normalize($name));

            if (! $match) {
                return;
            }

            $keepIds[] = (int) $match->id;
        }

        $keepIds = array_values(array_unique($keepIds));

        if (count($keepIds) !== count($this->pdfPrograms())) {
            return;
        }

        DB::transaction(function () use ($keepIds) {
            DB::table('programs')
                ->whereNotIn('id', $keepIds)
                ->delete();
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        // Non-PDF legacy programs were intentionally removed after confirming the 2026/2027 PDF set.
    }

    private function normalize(string $value): string
    {
        $value = strtolower($value);
        $value = str_replace(['&', '–', '—', '-', '(', ')', ',', ':'], ' ', $value);
        $value = preg_replace('/\b(and|of|the|in|by|their|for)\b/', ' ', $value);
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value);

        return trim(preg_replace('/\s+/', ' ', $value));
    }

    private function pdfPrograms(): array
    {
        return [
            ['60210400', 'Design: Footwear and Accessories Design'],
            ['60210400', 'Design: Clothing and Textile Design'],
            ['60210400', 'Design: Textile and Light Industry Design'],
            ['60410100', 'Economics'],
            ['60410200', 'Accounting'],
            ['60410500', 'Finance and Financial Technologies'],
            ['60410800', 'Management'],
            ['60411200', 'Marketing'],
            ['60520200', 'Ecology and Environmental Protection'],
            ['60530400', 'Hydrology'],
            ['60610100', 'Information Systems and Technologies'],
            ['60610300', 'Computer Engineering'],
            ['60610400', 'Software Engineering'],
            ['60610500', 'Artificial Intelligence'],
            ['60710100', 'Chemical Engineering'],
            ['60710200', 'Biotechnology'],
            ['60710400', 'Energy Engineering'],
            ['60710500', 'Electrical Engineering'],
            ['60710600', 'Hydropower Engineering'],
            ['60710800', 'Metrology and Standardization'],
            ['60710900', 'Automation of Technological Processes and Production'],
            ['60711000', 'Mechatronics and Robotics'],
            ['60711100', 'Biomedical Engineering'],
            ['60711400', 'Vehicle Engineering'],
            ['60712000', 'Renewable Energy Sources'],
            ['60720100', 'Food Technology'],
            ['60720200', 'Perfumery and Cosmetic Products Technology'],
            ['60720400', 'Technological Machines and Equipment'],
            ['60720500', 'Light Industry Production Technology'],
            ['60720600', 'Oil and Oil-Gas Processing Technology'],
            ['60720700', 'Light Industry Engineering'],
            ['60720900', 'Geology, Prospecting and Exploration of Mineral Resources'],
            ['60721100', 'Oil and Gas Engineering'],
            ['60721500', 'Geodesy and Geomatics'],
            ['60721600', 'Cartography and Remote Sensing'],
            ['60721700', 'Cadastre'],
            ['60721800', 'Manufacturing Engineering'],
            ['60730100', 'Architecture'],
            ['60730300', 'Civil Engineering'],
            ['60730500', 'Road Engineering'],
            ['60730600', 'Hydraulic and Geotechnical Engineering'],
            ['60730800', 'Reconstruction and Restoration of Architectural Monuments'],
            ['60730900', 'Urban Construction and Planning'],
            ['60731100', 'Production of Construction Materials, Products and Structures'],
            ['60810100', 'Agricultural Mechanization'],
            ['60810700', 'Technology of Storage and Processing of Agricultural Products'],
            ['60811000', 'Fruit and Vegetable Growing and Viticulture'],
            ['60811200', 'Water Management and Land Reclamation'],
            ['60811300', 'Operation of Hydraulic Structures and Pumping Stations'],
            ['60811400', 'Reclamation Hydrogeology'],
            ['60811500', 'Water Supply Engineering Systems'],
            ['60811600', 'Land Cadastre and Land Management'],
            ['61010100', 'Tourism and Hospitality'],
            ['61010400', 'Logistics'],
            ['61020000', 'Occupational Safety and Technical Safety'],
        ];
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->photos() as $slug => $photo) {
            DB::table('staff_profiles')
                ->where('slug', $slug)
                ->update([
                    'photo' => $photo,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->photos()) as $slug) {
            DB::table('staff_profiles')
                ->where('slug', $slug)
                ->update([
                    'photo' => null,
                    'updated_at' => now(),
                ]);
        }
    }

    private function photos(): array
    {
        return [
            'electrical-power-engineering-latipov-saidmurod-tuygunovich' => 'cms/staff/latipov-saidmurod-tuygunovich.jpg',
            'architecture-mirzaev-shamsiddin-rajabovich' => 'cms/staff/mirzaev-shamsiddin-rajabovich.jpg',
            'civil-engineering-inomjon-ilhomovich-tojiyev' => 'cms/staff/inomjon-ilhomovich-tojiyev.png',
            'light-industry-engineering-and-design-farhod-farmonovich-qazoqov' => 'cms/staff/farhod-farmonovich-qazoqov.jpg',
            'mechanics-engineering-graphics-fakhriddin-yusupovich-khabibov' => 'cms/staff/fakhriddin-yusupovich-khabibov.jpg',
            'technological-machines-equipment-uygun-abdullayevich-orinov' => 'cms/staff/uygun-abdullayevich-orinov.jpg',
        ];
    }
};

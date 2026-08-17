<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $photos = [
        'faculty-of-service-and-digitalization-dean-khayitov-sherbek-nayimovich' => 'cms/staff/Dr. Khayitov Sherbek Nayimovich.jpg',
        'faculty-of-service-and-digitalization-khayitov-sherbek-nayimovich' => 'cms/staff/Dr. Khayitov Sherbek Nayimovich.jpg',
        'faculty-of-service-and-digitalization-academic-boboqulov-farxod-baxtiyorivich' => 'cms/staff/Boboqulov Farxod Baxtiyorivich.jpg',
        'faculty-of-service-and-digitalization-farhod-bakhtiyorovich-boboqulov' => 'cms/staff/Boboqulov Farxod Baxtiyorivich.jpg',
        'faculty-of-service-and-digitalization-youth-fayzullayev-asqar-rajabboevich' => 'cms/staff/Fayzullayev Asqar Rajabboevich.jpg',
        'faculty-of-service-and-digitalization-fayzullayev-askar-rajabboevich' => 'cms/staff/Fayzullayev Asqar Rajabboevich.jpg',
    ];

    public function up(): void
    {
        foreach ($this->photos as $slug => $photo) {
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
        DB::table('staff_profiles')
            ->whereIn('slug', array_keys($this->photos))
            ->update([
                'photo' => null,
                'updated_at' => now(),
            ]);
    }
};

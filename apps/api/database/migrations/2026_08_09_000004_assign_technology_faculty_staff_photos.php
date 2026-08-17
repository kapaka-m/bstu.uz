<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $photos = [
        'faculty-of-technology-dean-adizov-rashid-tukhtayevich' => 'cms/staff/Dr. Adizov Rashid Tokhtayevich.jpg',
        'faculty-of-technology-adizov-rashid-tokhtayevich' => 'cms/staff/Dr. Adizov Rashid Tokhtayevich.jpg',
        'faculty-of-technology-academic-safarov-jasur-alijon-ogli' => 'cms/staff/Safarov Jasur Alijon o‘g‘li.jpg',
        'faculty-of-technology-safarov-jasur-alijon-ogli' => 'cms/staff/Safarov Jasur Alijon o‘g‘li.jpg',
        'faculty-of-technology-youth-bozorov-dilmurod-kholmurodovich' => 'cms/staff/Bozorov Dilmurod Kholmurodovich.jpg',
        'faculty-of-technology-bozorov-dilmurod-xolmurodovich' => 'cms/staff/Bozorov Dilmurod Kholmurodovich.jpg',
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

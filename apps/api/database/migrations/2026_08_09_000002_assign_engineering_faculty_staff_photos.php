<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $photos = [
        'faculty-of-engineering-dean-xojiyev-aziz-xolmurodovich' => 'cms/staff/khojiyev-aziz-kholmurodovich.jpg',
        'faculty-of-engineering-academic-rustamov-bobir-ismatovich' => 'cms/staff/rustamov-bobir-ismatovich.jpg',
        'faculty-of-engineering-youth-ashurov-asrorjon-komilovich' => 'cms/staff/ashurov-asrorjon-komilovich.jpg',
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

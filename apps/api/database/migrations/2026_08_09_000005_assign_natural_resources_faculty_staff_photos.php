<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $photos = [
        'faculty-of-natural-resources-management-dean-qobulova-barno-bakhriddin-qizi' => 'cms/staff/Dr. Barno Bakriddin Kobulova.jpg',
        'faculty-of-natural-resources-management-qobulova-barno-baxriddin-qizi' => 'cms/staff/Dr. Barno Bakriddin Kobulova.jpg',
        'faculty-of-natural-resources-management-youth-gadoyeva-abera-hasanovna' => 'cms/staff/Gadoyeva Abera Hasanovna.jpg',
        'faculty-of-natural-resources-management-gadoyeva-abera-hasanovna' => 'cms/staff/Gadoyeva Abera Hasanovna.jpg',
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

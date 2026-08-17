<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private string $departmentSlug = 'hydrotechnical-structures-pump-stations';

    private string $profileSlug = 'hydrotechnical-structures-pump-stations-hydraulic-structures-and-pumping-stations';

    private string $photo = 'cms/staff/axmedov-sharifboy-roziyevich.svg';

    public function up(): void
    {
        $departmentId = DB::table('departments')->where('slug', $this->departmentSlug)->value('id');

        DB::table('departments')
            ->where('slug', $this->departmentSlug)
            ->update([
                'head_name' => 'Axmedov Sharifboy Ro‘ziyevich',
                'email' => null,
                'phone' => '+998 91 444 72 27',
                'reception_time' => 'Monday-Friday 14:00-16:00',
                'updated_at' => now(),
            ]);

        DB::table('staff_profiles')
            ->where('slug', $this->profileSlug)
            ->update([
                'department_id' => $departmentId,
                'photo' => $this->photo,
                'email' => null,
                'phone' => '+998 91 444 72 27',
                'sort_order' => 1,
                'is_active' => true,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('staff_profiles')
            ->where('slug', $this->profileSlug)
            ->update([
                'photo' => null,
                'updated_at' => now(),
            ]);
    }
};

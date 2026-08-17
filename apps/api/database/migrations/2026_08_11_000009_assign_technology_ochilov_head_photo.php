<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private string $departmentSlug = 'oil-gas-refining-technology';

    private string $photo = 'cms/staff/ochilov-abduraxim-abdurasulovich.jpg';

    private array $profileSlugs = [
        'oil-gas-refining-technology-0-ochilov-abduraxim-abdurasulovich',
        'oil-gas-refining-technology-ochilov-abduraxim-abdurasulovich',
        'oil-gas-refining-technology-8-ochilov-abduraxim-abdurasulovich',
    ];

    public function up(): void
    {
        $departmentId = DB::table('departments')->where('slug', $this->departmentSlug)->value('id');

        DB::table('staff_profiles')
            ->whereIn('slug', $this->profileSlugs)
            ->when($departmentId, function ($query) use ($departmentId) {
                $query->orWhere(function ($inner) use ($departmentId) {
                    $inner->where('department_id', $departmentId)
                        ->where('email', 'ochilov82@gmail.ru');
                });
            })
            ->update([
                'photo' => $this->photo,
                'email' => 'ochilov82@gmail.ru',
                'phone' => '+998 91 411 00 16',
                'updated_at' => now(),
            ]);

        if ($departmentId) {
            DB::table('departments')
                ->where('id', $departmentId)
                ->update([
                    'head_name' => 'Ochilov Abduraxim Abdurasulovich',
                    'email' => 'ochilov82@gmail.ru',
                    'phone' => '+998 91 411 00 16',
                    'reception_time' => 'Monday-Friday 14:00-16:00',
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        DB::table('staff_profiles')
            ->whereIn('slug', $this->profileSlugs)
            ->update([
                'photo' => null,
                'updated_at' => now(),
            ]);
    }
};

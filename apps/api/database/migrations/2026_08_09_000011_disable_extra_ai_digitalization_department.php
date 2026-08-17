<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private string $slug = 'artificial-intelligence-digitalization';

    public function up(): void
    {
        $departmentId = DB::table('departments')->where('slug', $this->slug)->value('id');

        DB::table('departments')
            ->where('slug', $this->slug)
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);

        if ($departmentId) {
            DB::table('staff_profiles')
                ->where('department_id', $departmentId)
                ->update([
                    'is_active' => false,
                    'updated_at' => now(),
                ]);
        }

        DB::table('menu_items')
            ->where('url', '/department/'.$this->slug)
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        $departmentId = DB::table('departments')->where('slug', $this->slug)->value('id');

        DB::table('departments')
            ->where('slug', $this->slug)
            ->update([
                'is_active' => true,
                'updated_at' => now(),
            ]);

        if ($departmentId) {
            DB::table('staff_profiles')
                ->where('department_id', $departmentId)
                ->update([
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
        }

        DB::table('menu_items')
            ->where('url', '/department/'.$this->slug)
            ->update([
                'is_active' => true,
                'updated_at' => now(),
            ]);
    }
};

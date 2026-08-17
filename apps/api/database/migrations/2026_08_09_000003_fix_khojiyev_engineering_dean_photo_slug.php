<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('staff_profiles')
            ->whereIn('slug', [
                'faculty-of-engineering-dean-xojiyev-aziz-kholmurodovich',
                'faculty-of-engineering-dean-xojiyev-aziz-xolmurodovich',
            ])
            ->update([
                'photo' => 'cms/staff/khojiyev-aziz-kholmurodovich.jpg',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('staff_profiles')
            ->whereIn('slug', [
                'faculty-of-engineering-dean-xojiyev-aziz-kholmurodovich',
                'faculty-of-engineering-dean-xojiyev-aziz-xolmurodovich',
            ])
            ->update([
                'photo' => null,
                'updated_at' => now(),
            ]);
    }
};

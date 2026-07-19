<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('web_footers', 'admissions_apply_url')) {
            DB::statement('ALTER TABLE web_footers MODIFY admissions_apply_url VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('web_footers', 'admissions_apply_url')) {
            DB::statement('ALTER TABLE web_footers MODIFY admissions_apply_url VARCHAR(255) NULL');
        }
    }
};

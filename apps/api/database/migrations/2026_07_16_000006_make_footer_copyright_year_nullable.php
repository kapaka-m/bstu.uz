<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('web_footers', 'copyright_year')) {
            DB::statement('ALTER TABLE web_footers MODIFY copyright_year SMALLINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('web_footers', 'copyright_year')) {
            DB::statement('ALTER TABLE web_footers MODIFY copyright_year SMALLINT UNSIGNED NULL');
        }
    }
};

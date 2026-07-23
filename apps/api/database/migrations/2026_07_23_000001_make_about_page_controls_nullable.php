<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE about_pages MODIFY hero_contact_url VARCHAR(255) NULL DEFAULT NULL');
        DB::statement('ALTER TABLE about_pages MODIFY hero_campus_url VARCHAR(255) NULL DEFAULT NULL');
        DB::statement('ALTER TABLE about_pages MODIFY rector_profile_slug VARCHAR(255) NULL DEFAULT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE about_pages MODIFY hero_contact_url VARCHAR(255) NULL DEFAULT NULL');
        DB::statement('ALTER TABLE about_pages MODIFY hero_campus_url VARCHAR(255) NULL DEFAULT NULL');
        DB::statement('ALTER TABLE about_pages MODIFY rector_profile_slug VARCHAR(255) NULL DEFAULT NULL');
    }
};

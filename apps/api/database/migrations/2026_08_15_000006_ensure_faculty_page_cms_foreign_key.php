<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable('faculty_page_settings')
            || ! Schema::hasTable('faculty_page_setting_translations')
            || $this->hasConstraint()
        ) {
            return;
        }

        DB::statement(
            'ALTER TABLE faculty_page_setting_translations '.
            'ADD CONSTRAINT faculty_page_cms_setting_fk '.
            'FOREIGN KEY (faculty_page_setting_id) REFERENCES faculty_page_settings(id) '.
            'ON DELETE CASCADE',
        );
    }

    public function down(): void
    {
        if (
            ! Schema::hasTable('faculty_page_setting_translations')
            || ! $this->hasConstraint()
        ) {
            return;
        }

        DB::statement('ALTER TABLE faculty_page_setting_translations DROP FOREIGN KEY faculty_page_cms_setting_fk');
    }

    private function hasConstraint(): bool
    {
        $rows = DB::select(
            "SELECT CONSTRAINT_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND CONSTRAINT_NAME = ?
               AND REFERENCED_TABLE_NAME = ?",
            [
                'faculty_page_setting_translations',
                'faculty_page_cms_setting_fk',
                'faculty_page_settings',
            ],
        );

        return count($rows) > 0;
    }
};

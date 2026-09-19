<?php

use Database\Seeders\InterfaceTranslationSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        (new InterfaceTranslationSeeder)->run();
    }

    public function down(): void
    {
        // Preserve CMS edits and translations when rolling back code.
    }
};

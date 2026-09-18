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
        // Keep CMS-managed translations when rolling back application code.
    }
};

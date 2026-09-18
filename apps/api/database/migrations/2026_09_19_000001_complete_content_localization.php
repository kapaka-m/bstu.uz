<?php

use Database\Seeders\CompleteContentLocalizationSeeder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        (new CompleteContentLocalizationSeeder)->run();
    }

    public function down(): void
    {
        // Preserve reviewed translations; rollback must not restore incorrect English copies.
    }
};

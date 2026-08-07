<?php

use App\Models\AboutPage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable('about_pages')
            || ! Schema::hasTable('about_page_translations')
            || ! Schema::hasTable('about_page_content_entries')
            || ! Schema::hasTable('about_page_content_entry_translations')
        ) {
            return;
        }

        AboutPage::with('contentEntries.translations')
            ->get()
            ->each
            ->syncTranslationsMirror();
    }

    public function down(): void
    {
        //
    }
};

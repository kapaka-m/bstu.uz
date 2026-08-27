<?php

use App\Models\AboutPage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $pageId = DB::table('about_pages')->where('key', 'main')->value('id');

        if (! $pageId) {
            return;
        }

        $entry = DB::table('about_page_content_entries')
            ->where('about_page_id', $pageId)
            ->where('path', 'hero.badge')
            ->first();

        if (! $entry) {
            $entryId = DB::table('about_page_content_entries')->insertGetId([
                'about_page_id' => $pageId,
                'path' => 'hero.badge',
                'value_type' => 'text',
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $entryId = $entry->id;
            DB::table('about_page_content_entries')->where('id', $entryId)->update([
                'value_type' => 'text',
                'is_active' => true,
                'updated_at' => now(),
            ]);
        }

        foreach ($this->translations() as $locale => $value) {
            DB::table('about_page_content_entry_translations')->updateOrInsert(
                ['about_page_content_entry_id' => $entryId, 'locale' => $locale],
                ['value' => $value, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        AboutPage::with('contentEntries.translations')->find($pageId)?->syncTranslationsMirror();
        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        $pageId = DB::table('about_pages')->where('key', 'main')->value('id');

        if (! $pageId) {
            return;
        }

        $entryId = DB::table('about_page_content_entries')
            ->where('about_page_id', $pageId)
            ->where('path', 'hero.badge')
            ->value('id');

        if (! $entryId) {
            return;
        }

        DB::table('about_page_content_entry_translations')->updateOrInsert(
            ['about_page_content_entry_id' => $entryId, 'locale' => 'en'],
            ['value' => 'Pioneering Engineering Excellence', 'created_at' => now(), 'updated_at' => now()]
        );

        foreach (['uz', 'ru', 'ar'] as $locale) {
            DB::table('about_page_content_entry_translations')
                ->where('about_page_content_entry_id', $entryId)
                ->where('locale', $locale)
                ->delete();
        }

        AboutPage::with('contentEntries.translations')->find($pageId)?->syncTranslationsMirror();
        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    private function translations(): array
    {
        return [
            'en' => 'Pioneering Engineering Excellence',
            'uz' => 'Muhandislik salohiyati yetakchisi',
            'ru' => 'Лидер инженерного совершенства',
            'ar' => 'ريادة التميز الهندسي',
        ];
    }
};

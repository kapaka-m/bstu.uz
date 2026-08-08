<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['news', 'announcements', 'green_campus_articles', 'videos'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                if (! Schema::hasColumn($table, 'publisher_id')) {
                    $afterColumn = $table === 'videos' ? 'thumbnail' : 'image';
                    $blueprint->foreignId('publisher_id')
                        ->nullable()
                        ->after($afterColumn)
                        ->constrained('blog_departments')
                        ->nullOnDelete();
                }
            });
        }

        $this->assignAnnouncementPublishers();
        $this->assignGreenCampusPublishers();
        $this->assignVideoPublishers();
        $this->assignNewsPublishers();

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        foreach (['news', 'announcements', 'green_campus_articles', 'videos'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                if (Schema::hasColumn($table, 'publisher_id')) {
                    $blueprint->dropConstrainedForeignId('publisher_id');
                }
            });
        }
    }

    private function assignAnnouncementPublishers(): void
    {
        $name = DB::table('announcement_setting_translations')
            ->whereNotNull('publisher_name')
            ->where('publisher_name', '!=', '')
            ->orderByRaw("locale = 'en' desc")
            ->value('publisher_name') ?: 'BSTU Administration';

        $publisherId = $this->publisherId($name, 'cms/blog/blog-author.jpg');
        DB::table('announcements')->whereNull('publisher_id')->update([
            'publisher_id' => $publisherId,
            'updated_at' => now(),
        ]);
    }

    private function assignGreenCampusPublishers(): void
    {
        $articles = DB::table('green_campus_articles')
            ->leftJoin('green_campus_article_translations', function ($join) {
                $join->on('green_campus_articles.id', '=', 'green_campus_article_translations.green_campus_article_id')
                    ->where('green_campus_article_translations.locale', '=', 'en');
            })
            ->select('green_campus_articles.id', 'green_campus_articles.image', 'green_campus_article_translations.author')
            ->get();

        foreach ($articles as $article) {
            $name = trim((string) $article->author);
            if ($name === '') {
                $name = 'BSTU Communications Office';
            }

            DB::table('green_campus_articles')->where('id', $article->id)->update([
                'publisher_id' => $this->publisherId($name, 'cms/blog/blog-author.jpg'),
                'updated_at' => now(),
            ]);
        }
    }

    private function assignVideoPublishers(): void
    {
        $name = DB::table('video_gallery_setting_translations')
            ->whereNotNull('channel_name')
            ->where('channel_name', '!=', '')
            ->orderByRaw("locale = 'en' desc")
            ->value('channel_name') ?: 'Bukhara State Technical University';

        $publisherId = $this->publisherId($name, 'cms/blog/blog-author.jpg');
        DB::table('videos')->whereNull('publisher_id')->update([
            'publisher_id' => $publisherId,
            'updated_at' => now(),
        ]);
    }

    private function assignNewsPublishers(): void
    {
        $publisherId = $this->publisherId('BSTU Communications Office', 'cms/blog/blog-author.jpg');
        DB::table('news')->whereNull('publisher_id')->update([
            'publisher_id' => $publisherId,
            'updated_at' => now(),
        ]);
    }

    private function publisherId(string $name, ?string $image = null): int
    {
        $slug = Str::slug($name);
        $id = DB::table('blog_departments')->where('slug', $slug)->value('id');

        if (! $id) {
            $id = DB::table('blog_departments')->insertGetId([
                'slug' => $slug,
                'image' => $image,
                'sort_order' => (int) DB::table('blog_departments')->max('sort_order') + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($this->locales() as $locale) {
            DB::table('blog_department_translations')->updateOrInsert(
                ['blog_department_id' => $id, 'locale' => $locale],
                [
                    'name' => $name,
                    'meta_title' => $name,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        return (int) $id;
    }

    private function locales(): array
    {
        return DB::table('locales')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter()
            ->values()
            ->all() ?: ['en', 'uz', 'ru', 'ar'];
    }
};

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BlogDepartmentSeeder extends Seeder
{
    public function run(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('blog_departments')) {
            return;
        }

        $locales = DB::table('locales')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->pluck('code')
            ->filter()
            ->values()
            ->all() ?: ['en', 'uz', 'ru', 'ar'];

        $blogs = DB::table('blogs')
            ->leftJoin('blog_translations', function ($join) {
                $join->on('blogs.id', '=', 'blog_translations.blog_id')
                    ->where('blog_translations.locale', '=', 'en');
            })
            ->select('blogs.id', 'blogs.author', 'blogs.author_image', 'blog_translations.author as translated_author')
            ->get();

        foreach ($blogs as $blog) {
            $name = trim((string) ($blog->translated_author ?: $blog->author));
            $slug = Str::slug($name);
            if ($name === '' || $slug === '') {
                continue;
            }

            $departmentId = DB::table('blog_departments')->where('slug', $slug)->value('id');
            if (! $departmentId) {
                $departmentId = DB::table('blog_departments')->insertGetId([
                    'slug' => $slug,
                    'image' => $blog->author_image,
                    'sort_order' => (int) $blog->id,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($locales as $locale) {
                DB::table('blog_department_translations')->updateOrInsert(
                    ['blog_department_id' => $departmentId, 'locale' => $locale],
                    [
                        'name' => $name,
                        'meta_title' => $name,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

            DB::table('blogs')->where('id', $blog->id)->update([
                'blog_department_id' => $departmentId,
                'updated_at' => now(),
            ]);
        }

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }
}

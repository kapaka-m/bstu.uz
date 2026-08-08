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
        Schema::create('blog_departments', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('blog_department_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_department_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
            $table->unique(['blog_department_id', 'locale']);
        });

        Schema::table('blogs', function (Blueprint $table) {
            $table->foreignId('blog_department_id')
                ->nullable()
                ->after('author_image')
                ->constrained('blog_departments')
                ->nullOnDelete();
        });

        $this->syncFromExistingBlogs();

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('blog_department_id');
        });

        Schema::dropIfExists('blog_department_translations');
        Schema::dropIfExists('blog_departments');
    }

    private function syncFromExistingBlogs(): void
    {
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
            if ($name === '') {
                continue;
            }

            $slug = Str::slug($name);
            if ($slug === '') {
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
                        'description' => null,
                        'meta_title' => $name,
                        'meta_description' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }

            DB::table('blogs')->where('id', $blog->id)->update([
                'blog_department_id' => $departmentId,
                'updated_at' => now(),
            ]);
        }
    }
};

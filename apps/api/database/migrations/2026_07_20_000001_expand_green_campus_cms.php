<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('green_campus_articles', function (Blueprint $table) {
            if (! Schema::hasColumn('green_campus_articles', 'category')) {
                $table->string('category')->nullable()->after('slug');
            }
            if (! Schema::hasColumn('green_campus_articles', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('views');
            }
            if (! Schema::hasColumn('green_campus_articles', 'is_published')) {
                $table->boolean('is_published')->default(true)->after('published_at');
            }
            if (! Schema::hasColumn('green_campus_articles', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_published');
            }
        });

        DB::table('green_campus_articles')
            ->leftJoin('green_campus_article_translations', function ($join) {
                $join->on('green_campus_articles.id', '=', 'green_campus_article_translations.green_campus_article_id')
                    ->where('green_campus_article_translations.locale', '=', 'en');
            })
            ->whereNull('green_campus_articles.category')
            ->update([
                'green_campus_articles.category' => DB::raw("COALESCE(green_campus_article_translations.category, '')"),
            ]);

        DB::table('green_campus_articles')
            ->whereNull('published_at')
            ->update([
                'green_campus_articles.published_at' => DB::raw('green_campus_articles.created_at'),
            ]);

        Schema::create('green_campus_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->default('main');
            $table->integer('home_limit')->default(3);
            $table->integer('recent_limit')->default(4);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('green_campus_setting_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('green_campus_setting_id')
                ->constrained('green_campus_settings', 'id', 'gc_setting_foreign')
                ->onDelete('cascade');
            $table->string('locale');
            $table->string('home_tag')->nullable();
            $table->string('home_title')->nullable();
            $table->string('view_all_label')->nullable();
            $table->string('read_more_label')->nullable();
            $table->string('search_title')->nullable();
            $table->string('search_placeholder')->nullable();
            $table->string('categories_title')->nullable();
            $table->string('recent_title')->nullable();
            $table->string('all_label')->nullable();
            $table->string('no_results_label')->nullable();
            $table->string('callout_title')->nullable();
            $table->text('callout_description')->nullable();
            $table->string('callout_cta_label')->nullable();
            $table->string('callout_email')->nullable();
            $table->string('views_label')->nullable();
            $table->string('gallery_label')->nullable();
            $table->string('related_label')->nullable();
            $table->string('close_viewer_label')->nullable();
            $table->string('previous_image_label')->nullable();
            $table->string('next_image_label')->nullable();
            $table->json('category_labels')->nullable();
            $table->timestamps();

            $table->unique(['green_campus_setting_id', 'locale'], 'gc_setting_locale_unique');
        });

        if (! DB::table('green_campus_settings')->where('key', 'main')->exists()) {
            DB::table('green_campus_settings')->insert([
                'key' => 'main',
                'home_limit' => 3,
                'recent_limit' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('green_campus_setting_translations');
        Schema::dropIfExists('green_campus_settings');

        Schema::table('green_campus_articles', function (Blueprint $table) {
            if (Schema::hasColumn('green_campus_articles', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
            if (Schema::hasColumn('green_campus_articles', 'is_published')) {
                $table->dropColumn('is_published');
            }
            if (Schema::hasColumn('green_campus_articles', 'published_at')) {
                $table->dropColumn('published_at');
            }
            if (Schema::hasColumn('green_campus_articles', 'category')) {
                $table->dropColumn('category');
            }
        });
    }
};

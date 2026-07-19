<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->string('author')->nullable();
            $table->string('author_image')->nullable();
            $table->string('category')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->boolean('is_published')->default(true)->index();
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->timestamps();
        });

        Schema::create('blog_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained('blogs')->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('title');
            $table->string('author')->nullable();
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();

            $table->unique(['blog_id', 'locale']);
        });

        Schema::create('blog_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained('blogs')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('blog_comments')->cascadeOnDelete();
            $table->string('author_name');
            $table->string('email')->nullable();
            $table->text('content');
            $table->boolean('is_approved')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('blog_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->unsignedTinyInteger('home_limit')->default(3);
            $table->unsignedTinyInteger('recent_limit')->default(5);
            $table->json('tags')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('blog_setting_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_setting_id')->constrained('blog_settings')->cascadeOnDelete();
            $table->string('locale', 5)->index();
            $table->string('home_tag')->nullable();
            $table->string('home_title')->nullable();
            $table->string('view_all_label')->nullable();
            $table->string('read_more_label')->nullable();
            $table->string('search_title')->nullable();
            $table->string('search_placeholder')->nullable();
            $table->string('categories_title')->nullable();
            $table->string('recent_title')->nullable();
            $table->string('tags_title')->nullable();
            $table->string('all_blog_label')->nullable();
            $table->string('loading_label')->nullable();
            $table->string('no_results_label')->nullable();
            $table->string('clear_filters_label')->nullable();
            $table->string('back_to_blog_label')->nullable();
            $table->string('comments_label')->nullable();
            $table->string('reply_label')->nullable();
            $table->string('form_title')->nullable();
            $table->string('form_name_label')->nullable();
            $table->string('form_email_label')->nullable();
            $table->string('form_comment_label')->nullable();
            $table->string('form_submit_label')->nullable();
            $table->timestamps();

            $table->unique(['blog_setting_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_setting_translations');
        Schema::dropIfExists('blog_settings');
        Schema::dropIfExists('blog_comments');
        Schema::dropIfExists('blog_translations');
        Schema::dropIfExists('blogs');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            if (! Schema::hasColumn('videos', 'video_type')) {
                $table->string('video_type')->default('youtube')->after('thumbnail');
            }
            if (! Schema::hasColumn('videos', 'youtube_id')) {
                $table->string('youtube_id')->nullable()->after('video_type');
            }
            if (! Schema::hasColumn('videos', 'duration')) {
                $table->string('duration')->nullable()->after('youtube_id');
            }
            if (! Schema::hasColumn('videos', 'views_count')) {
                $table->unsignedInteger('views_count')->default(0)->after('duration');
            }
            if (! Schema::hasColumn('videos', 'likes_count')) {
                $table->unsignedInteger('likes_count')->default(0)->after('views_count');
            }
            if (! Schema::hasColumn('videos', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('likes_count');
            }
        });

        Schema::table('video_translations', function (Blueprint $table) {
            if (! Schema::hasColumn('video_translations', 'category')) {
                $table->string('category')->nullable()->after('title');
            }
        });

        if (! Schema::hasTable('video_gallery_settings')) {
            Schema::create('video_gallery_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->unsignedSmallInteger('home_limit')->default(4);
                $table->unsignedInteger('subscriber_count')->default(0);
                $table->string('youtube_channel_url')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('video_gallery_setting_translations')) {
            Schema::create('video_gallery_setting_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('video_gallery_setting_id');
                $table->string('locale');
                $table->string('home_tag')->nullable();
                $table->string('home_title')->nullable();
                $table->text('home_subtitle')->nullable();
                $table->string('view_all_label')->nullable();
                $table->string('recommended_label')->nullable();
                $table->string('videos_label')->nullable();
                $table->string('description_title')->nullable();
                $table->string('show_more_label')->nullable();
                $table->string('show_less_label')->nullable();
                $table->string('like_label')->nullable();
                $table->string('liked_label')->nullable();
                $table->string('share_label')->nullable();
                $table->string('subscribe_label')->nullable();
                $table->string('subscribed_label')->nullable();
                $table->string('subscribers_label')->nullable();
                $table->string('link_copied_label')->nullable();
                $table->string('no_videos_label')->nullable();
                $table->json('category_labels')->nullable();
                $table->timestamps();

                $table->unique(['video_gallery_setting_id', 'locale'], 'video_gallery_setting_locale_unique');
                $table->foreign('video_gallery_setting_id', 'vgs_trans_setting_fk')
                    ->references('id')
                    ->on('video_gallery_settings')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('video_gallery_setting_translations');
        Schema::dropIfExists('video_gallery_settings');

        Schema::table('video_translations', function (Blueprint $table) {
            if (Schema::hasColumn('video_translations', 'category')) {
                $table->dropColumn('category');
            }
        });

        Schema::table('videos', function (Blueprint $table) {
            foreach (['video_type', 'youtube_id', 'duration', 'views_count', 'likes_count', 'published_at'] as $column) {
                if (Schema::hasColumn('videos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

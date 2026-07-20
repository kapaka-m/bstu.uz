<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            if (! Schema::hasColumn('announcements', 'views_count')) {
                $table->unsignedInteger('views_count')->default(0)->after('is_published');
            }
        });

        Schema::table('announcement_translations', function (Blueprint $table) {
            if (! Schema::hasColumn('announcement_translations', 'category_label')) {
                $table->string('category_label')->nullable()->after('locale');
            }
        });

        if (! Schema::hasTable('announcement_settings')) {
            Schema::create('announcement_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique()->default('main');
            $table->unsignedTinyInteger('home_limit')->default(4);
                $table->unsignedTinyInteger('recent_limit')->default(5);
                $table->unsignedTinyInteger('important_limit')->default(3);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('announcement_setting_translations')) {
            Schema::create('announcement_setting_translations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('announcement_setting_id');
                $table->string('locale', 5);
                $table->string('home_tag')->nullable();
                $table->string('home_title')->nullable();
                $table->string('view_all_label')->nullable();
                $table->string('read_details_label')->nullable();
                $table->string('search_title')->nullable();
                $table->string('search_placeholder')->nullable();
                $table->string('categories_title')->nullable();
                $table->string('recent_title')->nullable();
                $table->string('all_label')->nullable();
                $table->string('views_label')->nullable();
                $table->string('important_label')->nullable();
                $table->string('loading_label')->nullable();
                $table->string('no_results_label')->nullable();
                $table->string('clear_filters_label')->nullable();
                $table->string('share_label')->nullable();
                $table->string('copy_link_label')->nullable();
                $table->string('copied_label')->nullable();
                $table->string('published_by_label')->nullable();
                $table->string('publisher_name')->nullable();
                $table->timestamps();
                $table->unique(['announcement_setting_id', 'locale'], 'ann_setting_locale_unique');
                $table->foreign('announcement_setting_id', 'ann_setting_trans_setting_fk')
                    ->references('id')
                    ->on('announcement_settings')
                    ->cascadeOnDelete();
            });
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_setting_translations');
        Schema::dropIfExists('announcement_settings');

        Schema::table('announcements', function (Blueprint $table) {
            if (Schema::hasColumn('announcements', 'views_count')) {
                $table->dropColumn('views_count');
            }
        });

        Schema::table('announcement_translations', function (Blueprint $table) {
            if (Schema::hasColumn('announcement_translations', 'category_label')) {
                $table->dropColumn('category_label');
            }
        });
    }
};

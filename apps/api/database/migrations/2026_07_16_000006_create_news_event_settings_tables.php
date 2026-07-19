<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news_event_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->unsignedTinyInteger('home_limit')->default(4);
            $table->unsignedTinyInteger('recent_limit')->default(5);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('news_event_setting_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_event_setting_id')->constrained('news_event_settings')->onDelete('cascade');
            $table->string('locale', 5);
            $table->string('home_tag')->nullable();
            $table->string('home_title')->nullable();
            $table->text('home_subtitle')->nullable();
            $table->string('view_all_label')->nullable();
            $table->string('read_details_label')->nullable();
            $table->string('search_title')->nullable();
            $table->string('search_placeholder')->nullable();
            $table->string('categories_title')->nullable();
            $table->string('recent_title')->nullable();
            $table->string('all_news_label')->nullable();
            $table->string('news_label')->nullable();
            $table->string('events_label')->nullable();
            $table->string('views_label')->nullable();
            $table->string('loading_label')->nullable();
            $table->string('no_results_label')->nullable();
            $table->string('clear_filters_label')->nullable();
            $table->timestamps();

            $table->unique(['news_event_setting_id', 'locale'], 'news_event_settings_locale_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_event_setting_translations');
        Schema::dropIfExists('news_event_settings');
    }
};

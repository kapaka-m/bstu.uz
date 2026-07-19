<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('green_campus_stats', function (Blueprint $table) {
            $table->id();
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('green_campus_stat_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('green_campus_stat_id')
                ->constrained('green_campus_stats', 'id', 'gc_stat_foreign')
                ->onDelete('cascade');
            $table->string('locale');
            $table->string('value');
            $table->string('label');
            $table->timestamps();

            $table->unique(['green_campus_stat_id', 'locale'], 'gc_stat_id_locale_unique');
        });

        Schema::create('green_campus_articles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->json('gallery')->nullable();
            $table->integer('views')->default(0);
            $table->timestamps();
        });

        Schema::create('green_campus_article_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('green_campus_article_id')
                ->constrained('green_campus_articles', 'id', 'gc_art_foreign')
                ->onDelete('cascade');
            $table->string('locale');
            $table->string('title');
            $table->string('category')->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('author')->nullable();
            $table->timestamps();

            $table->unique(['green_campus_article_id', 'locale'], 'gc_art_id_locale_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('green_campus_article_translations');
        Schema::dropIfExists('green_campus_articles');
        Schema::dropIfExists('green_campus_stat_translations');
        Schema::dropIfExists('green_campus_stats');
    }
};

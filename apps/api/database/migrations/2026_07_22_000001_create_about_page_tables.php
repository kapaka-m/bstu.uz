<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_pages', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->default('main');
            $table->string('hero_contact_url')->nullable();
            $table->string('hero_campus_url')->nullable();
            $table->string('identity_image')->nullable();
            $table->string('rector_profile_slug')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('about_page_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('about_page_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->json('content')->nullable();
            $table->timestamps();

            $table->unique(['about_page_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_page_translations');
        Schema::dropIfExists('about_pages');
    }
};

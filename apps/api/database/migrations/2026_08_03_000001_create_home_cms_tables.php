<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_key')->unique();
            $table->string('section_type')->default('content');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('home_section_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_section_id')->constrained('home_sections')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('eyebrow')->nullable();
            $table->string('title')->nullable();
            $table->text('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('secondary_title')->nullable();
            $table->text('secondary_description')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();
            $table->string('image_alt')->nullable();
            $table->timestamps();

            $table->unique(['home_section_id', 'locale'], 'home_section_locale_unique');
        });

        Schema::create('home_section_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_section_id')->constrained('home_sections')->cascadeOnDelete();
            $table->string('item_key')->nullable();
            $table->string('icon')->nullable();
            $table->string('value')->nullable();
            $table->string('suffix')->nullable();
            $table->string('url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->index(['home_section_id', 'sort_order']);
        });

        Schema::create('home_section_item_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_section_item_id')->constrained('home_section_items')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('label')->nullable();
            $table->string('action_label')->nullable();
            $table->timestamps();

            $table->unique(['home_section_item_id', 'locale'], 'home_section_item_locale_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_section_item_translations');
        Schema::dropIfExists('home_section_items');
        Schema::dropIfExists('home_section_translations');
        Schema::dropIfExists('home_sections');
    }
};

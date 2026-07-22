<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_pages', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->default('main');
            $table->text('map_embed_url')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('contact_page_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_page_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->json('content')->nullable();
            $table->timestamps();

            $table->unique(['contact_page_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_page_translations');
        Schema::dropIfExists('contact_pages');
    }
};

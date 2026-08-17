<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apply_pages', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->boolean('is_published')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('apply_page_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apply_page_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->json('content');
            $table->timestamps();

            $table->unique(['apply_page_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apply_page_translations');
        Schema::dropIfExists('apply_pages');
    }
};

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
        Schema::create('university_centers', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('university_center_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('university_center_id')
                ->constrained('university_centers')
                ->onDelete('cascade');
            $table->string('locale');
            $table->string('name');
            $table->string('head');
            $table->string('head_title');
            $table->string('office_hours');
            $table->text('about');
            $table->json('functions')->nullable();
            $table->timestamps();

            $table->unique(['university_center_id', 'locale'], 'uc_translation_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('university_center_translations');
        Schema::dropIfExists('university_centers');
    }
};

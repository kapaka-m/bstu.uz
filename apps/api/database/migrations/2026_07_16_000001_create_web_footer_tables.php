<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_footers', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->default('main');
            $table->json('useful_links')->nullable();
            $table->json('faculty_links')->nullable();
            $table->json('social_links')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->unsignedSmallInteger('copyright_year')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('web_footer_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('web_footer_id')->constrained('web_footers')->onDelete('cascade');
            $table->string('locale', 5);
            $table->string('logo_alt')->nullable();
            $table->text('description')->nullable();
            $table->string('useful_links_title')->nullable();
            $table->string('faculties_title')->nullable();
            $table->string('contact_title')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('phone_label')->nullable();
            $table->string('email_label')->nullable();
            $table->string('rights_text')->nullable();
            $table->json('useful_link_labels')->nullable();
            $table->json('faculty_link_labels')->nullable();
            $table->timestamps();

            $table->unique(['web_footer_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_footer_translations');
        Schema::dropIfExists('web_footers');
    }
};

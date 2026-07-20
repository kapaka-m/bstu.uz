<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administration_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('photo')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('telegram_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_rector')->default(false);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('administration_profile_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('administration_profile_id');
            $table->string('locale', 5);
            $table->string('full_name');
            $table->string('position');
            $table->string('degree')->nullable();
            $table->string('office_hours')->nullable();
            $table->text('about')->nullable();
            $table->longText('details')->nullable();
            $table->json('achievements')->nullable();
            $table->timestamps();

            $table->unique(['administration_profile_id', 'locale'], 'admin_profile_locale_unique');
            $table->foreign('administration_profile_id', 'admin_profile_translation_fk')
                ->references('id')
                ->on('administration_profiles')
                ->onDelete('cascade');
        });

        Schema::create('administration_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->default('main');
            $table->unsignedInteger('home_limit')->default(6);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('administration_setting_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('administration_setting_id');
            $table->string('locale', 5);
            $table->string('home_tag')->nullable();
            $table->string('home_title')->nullable();
            $table->string('reception_label')->nullable();
            $table->string('phone_label')->nullable();
            $table->string('email_label')->nullable();
            $table->string('telegram_label')->nullable();
            $table->string('rector_bot_label')->nullable();
            $table->string('structure_title')->nullable();
            $table->timestamps();

            $table->unique(['administration_setting_id', 'locale'], 'admin_setting_locale_unique');
            $table->foreign('administration_setting_id', 'admin_setting_translation_fk')
                ->references('id')
                ->on('administration_settings')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administration_setting_translations');
        Schema::dropIfExists('administration_settings');
        Schema::dropIfExists('administration_profile_translations');
        Schema::dropIfExists('administration_profiles');
    }
};

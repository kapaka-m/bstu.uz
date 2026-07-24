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
        Schema::create('university_center_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('university_center_setting_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('center_setting_id')
                ->constrained('university_center_settings')
                ->onDelete('cascade');
            $table->string('locale');
            $table->string('sidebar_title');
            $table->string('structure_label');
            $table->string('about_label');
            $table->string('staff_label');
            $table->text('default_head_desc');
            $table->string('mission_label');
            $table->string('support_title');
            $table->text('support_desc');
            $table->string('contact_btn_label');
            $table->timestamps();

            $table->unique(['center_setting_id', 'locale'], 'ucs_translation_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('university_center_setting_translations');
        Schema::dropIfExists('university_center_settings');
    }
};

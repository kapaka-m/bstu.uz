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
        Schema::table('university_center_translations', function (Blueprint $table) {
            $table->text('head_description')->nullable()->after('head_title');
        });

        Schema::table('university_center_setting_translations', function (Blueprint $table) {
            $table->string('function_badge_label')->nullable()->after('contact_btn_label');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('university_center_translations', function (Blueprint $table) {
            $table->dropColumn('head_description');
        });

        Schema::table('university_center_setting_translations', function (Blueprint $table) {
            $table->dropColumn('function_badge_label');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->string('head_name')->nullable()->after('icon');
            $table->string('email')->nullable()->after('head_name');
            $table->string('phone')->nullable()->after('email');
            $table->string('reception_time')->nullable()->after('phone');
            $table->string('source_url')->nullable()->after('reception_time');
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->string('official_code')->nullable()->after('code');
            $table->string('track')->nullable()->after('official_code');
        });

        Schema::table('faculty_translations', function (Blueprint $table) {
            $table->json('content_sections')->nullable()->after('description');
        });

        Schema::table('department_translations', function (Blueprint $table) {
            $table->json('content_sections')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('department_translations', function (Blueprint $table) {
            $table->dropColumn('content_sections');
        });

        Schema::table('faculty_translations', function (Blueprint $table) {
            $table->dropColumn('content_sections');
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn(['official_code', 'track']);
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn(['head_name', 'email', 'phone', 'reception_time', 'source_url']);
        });
    }
};

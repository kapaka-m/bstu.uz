<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('id');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->string('title')->nullable()->after('filename');
            $table->text('alt_text')->nullable()->after('title');
            $table->string('type')->nullable()->after('alt_text');
            $table->boolean('is_public')->default(true)->after('size');
        });

        Schema::table('news', function (Blueprint $table) {
            $table->string('author')->nullable()->after('views_count');
            $table->string('author_image')->nullable()->after('author');
            $table->unsignedInteger('comments_count')->default(0)->after('author_image');
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['author', 'author_image', 'comments_count']);
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn(['title', 'alt_text', 'type', 'is_public']);
        });

        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};

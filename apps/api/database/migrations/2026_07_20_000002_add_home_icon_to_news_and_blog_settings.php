<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_event_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('news_event_settings', 'home_icon')) {
                $table->string('home_icon')->default('newspaper')->after('recent_limit');
            }
        });

        Schema::table('blog_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('blog_settings', 'home_icon')) {
                $table->string('home_icon')->default('book-open')->after('recent_limit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('blog_settings', function (Blueprint $table) {
            if (Schema::hasColumn('blog_settings', 'home_icon')) {
                $table->dropColumn('home_icon');
            }
        });

        Schema::table('news_event_settings', function (Blueprint $table) {
            if (Schema::hasColumn('news_event_settings', 'home_icon')) {
                $table->dropColumn('home_icon');
            }
        });
    }
};

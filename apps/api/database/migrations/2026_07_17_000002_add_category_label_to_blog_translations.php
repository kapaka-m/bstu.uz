<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_translations', function (Blueprint $table) {
            $table->string('category_label')->nullable()->after('author');
        });
    }

    public function down(): void
    {
        Schema::table('blog_translations', function (Blueprint $table) {
            $table->dropColumn('category_label');
        });
    }
};

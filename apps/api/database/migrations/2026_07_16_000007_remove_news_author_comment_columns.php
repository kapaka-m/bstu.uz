<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $columns = array_filter(
                ['author', 'author_image', 'comments_count'],
                fn ($column) => Schema::hasColumn('news', $column)
            );

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            if (! Schema::hasColumn('news', 'author')) {
                $table->string('author')->nullable()->after('views_count');
            }
            if (! Schema::hasColumn('news', 'author_image')) {
                $table->string('author_image')->nullable()->after('author');
            }
            if (! Schema::hasColumn('news', 'comments_count')) {
                $table->unsignedInteger('comments_count')->default(0)->after('author_image');
            }
        });
    }
};

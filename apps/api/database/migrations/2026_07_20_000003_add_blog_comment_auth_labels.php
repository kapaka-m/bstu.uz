<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_setting_translations', function (Blueprint $table) {
            foreach ([
                'comment_login_title',
                'comment_login_text',
                'comment_login_action',
                'signed_in_as_label',
            ] as $column) {
                if (! Schema::hasColumn('blog_setting_translations', $column)) {
                    $table->string($column)->nullable()->after('form_submit_label');
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('blog_setting_translations', function (Blueprint $table) {
            foreach ([
                'signed_in_as_label',
                'comment_login_action',
                'comment_login_text',
                'comment_login_title',
            ] as $column) {
                if (Schema::hasColumn('blog_setting_translations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

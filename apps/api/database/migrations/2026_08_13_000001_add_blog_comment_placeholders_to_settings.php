<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_setting_translations', function (Blueprint $table) {
            if (! Schema::hasColumn('blog_setting_translations', 'form_comment_placeholder')) {
                $table->string('form_comment_placeholder')->nullable()->after('form_comment_label');
            }

            if (! Schema::hasColumn('blog_setting_translations', 'form_reply_placeholder')) {
                $table->string('form_reply_placeholder')->nullable()->after('form_comment_placeholder');
            }
        });

        foreach ($this->translations() as $locale => $fields) {
            DB::table('blog_setting_translations')
                ->where('locale', $locale)
                ->update($fields + ['updated_at' => now()]);
        }

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        Schema::table('blog_setting_translations', function (Blueprint $table) {
            if (Schema::hasColumn('blog_setting_translations', 'form_reply_placeholder')) {
                $table->dropColumn('form_reply_placeholder');
            }

            if (Schema::hasColumn('blog_setting_translations', 'form_comment_placeholder')) {
                $table->dropColumn('form_comment_placeholder');
            }
        });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    private function translations(): array
    {
        return [
            'en' => [
                'form_comment_placeholder' => 'Write your comment',
                'form_reply_placeholder' => 'Write your reply',
            ],
            'uz' => [
                'form_comment_placeholder' => 'Izohingizni yozing',
                'form_reply_placeholder' => 'Javobingizni yozing',
            ],
            'ru' => [
                'form_comment_placeholder' => 'Напишите комментарий',
                'form_reply_placeholder' => 'Напишите ответ',
            ],
            'ar' => [
                'form_comment_placeholder' => 'اكتب تعليقك',
                'form_reply_placeholder' => 'اكتب ردك',
            ],
        ];
    }
};

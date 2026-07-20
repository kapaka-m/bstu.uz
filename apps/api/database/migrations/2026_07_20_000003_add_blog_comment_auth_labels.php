<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        $labels = [
            'en' => [
                'comment_login_title' => 'Sign in to comment',
                'comment_login_text' => 'Comments are visible to everyone. Please sign in before adding a comment or reply.',
                'comment_login_action' => 'Sign In',
                'signed_in_as_label' => 'Signed in as',
            ],
            'uz' => [
                'comment_login_title' => 'Izoh yozish uchun tizimga kiring',
                'comment_login_text' => 'Izohlar hamma uchun ko‘rinadi. Izoh yoki javob qo‘shish uchun avval tizimga kiring.',
                'comment_login_action' => 'Tizimga kirish',
                'signed_in_as_label' => 'Tizimga kirgan foydalanuvchi',
            ],
            'ru' => [
                'comment_login_title' => 'Войдите, чтобы оставить комментарий',
                'comment_login_text' => 'Комментарии видны всем. Чтобы добавить комментарий или ответ, сначала войдите в систему.',
                'comment_login_action' => 'Войти',
                'signed_in_as_label' => 'Вы вошли как',
            ],
            'ar' => [
                'comment_login_title' => 'سجّل الدخول لإضافة تعليق',
                'comment_login_text' => 'التعليقات ظاهرة لجميع الزوار. لإضافة تعليق أو رد يجب تسجيل الدخول أولًا.',
                'comment_login_action' => 'تسجيل الدخول',
                'signed_in_as_label' => 'تم تسجيل الدخول باسم',
            ],
        ];

        foreach ($labels as $locale => $values) {
            DB::table('blog_setting_translations')
                ->where('locale', $locale)
                ->update($values);
        }
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $translations = [
            'auth.resetPasswordSubtitle' => [
                'en' => 'Enter your new password to regain access to your account.',
                'uz' => 'Hisobingizga qayta kirish uchun yangi parolingizni kiriting.',
                'ru' => 'Введите новый пароль, чтобы восстановить доступ к аккаунту.',
                'ar' => 'أدخل كلمة المرور الجديدة لاستعادة الوصول إلى حسابك.',
            ],
            'common.cancel' => [
                'en' => 'Cancel',
                'uz' => 'Bekor qilish',
                'ru' => 'Отмена',
                'ar' => 'إلغاء',
            ],
            'common.noResultsFound' => [
                'en' => 'No results found',
                'uz' => 'Natijalar topilmadi',
                'ru' => 'Результаты не найдены',
                'ar' => 'لم يتم العثور على نتائج',
            ],
            'common.programCode' => [
                'en' => 'Program code',
                'uz' => 'Dastur kodi',
                'ru' => 'Код программы',
                'ar' => 'كود البرنامج',
            ],
            'common.save' => [
                'en' => 'Save',
                'uz' => 'Saqlash',
                'ru' => 'Сохранить',
                'ar' => 'حفظ',
            ],
            'common.semester' => [
                'en' => 'Semester',
                'uz' => 'Semestr',
                'ru' => 'Семестр',
                'ar' => 'الفصل الدراسي',
            ],
            'common.studyMode' => [
                'en' => 'Study mode',
                'uz' => 'Taʼlim shakli',
                'ru' => 'Форма обучения',
                'ar' => 'نظام الدراسة',
            ],
            'common.year' => [
                'en' => 'Year',
                'uz' => 'Yil',
                'ru' => 'Год',
                'ar' => 'السنة',
            ],
        ];

        foreach ($translations as $fullKey => $values) {
            [$group, $key] = explode('.', $fullKey, 2);
            $translationKeyId = DB::table('translation_keys')->where('group', $group)->where('key', $key)->value('id');

            if (! $translationKeyId) {
                $translationKeyId = DB::table('translation_keys')->insertGetId([
                    'group' => $group,
                    'key' => $key,
                    'description' => 'Runtime UI label: '.$fullKey,
                    'is_system' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($values as $locale => $value) {
                DB::table('translation_values')->updateOrInsert(
                    ['translation_key_id' => $translationKeyId, 'locale' => $locale],
                    ['value' => $value, 'updated_at' => now(), 'created_at' => now()]
                );
            }
        }

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        foreach ([
            'auth.resetPasswordSubtitle',
            'common.cancel',
            'common.noResultsFound',
            'common.programCode',
            'common.save',
            'common.semester',
            'common.studyMode',
            'common.year',
        ] as $fullKey) {
            [$group, $key] = explode('.', $fullKey, 2);
            $id = DB::table('translation_keys')->where('group', $group)->where('key', $key)->value('id');
            if ($id) {
                DB::table('translation_values')->where('translation_key_id', $id)->delete();
                DB::table('translation_keys')->where('id', $id)->delete();
            }
        }
    }
};

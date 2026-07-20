<?php

namespace Database\Seeders;

use App\Models\AdministrationProfile;
use App\Models\AdministrationSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class AdministrationSeeder extends Seeder
{
    public function run(): void
    {
        $setting = AdministrationSetting::updateOrCreate(
            ['key' => 'main'],
            ['home_limit' => 6, 'is_active' => true]
        );

        $settingTranslations = [
            'en' => [
                'home_tag' => 'University Leadership',
                'home_title' => 'BSTU Executive Administration',
                'reception_label' => 'Reception Days:',
                'phone_label' => 'Phone:',
                'email_label' => 'Email:',
                'telegram_label' => 'Telegram:',
                'rector_bot_label' => 'Rector Telegram Bot:',
                'structure_title' => 'Administration',
            ],
            'uz' => [
                'home_tag' => 'Universitet rahbariyati',
                'home_title' => 'BuxDTU ijrochi maʼmuriyati',
                'reception_label' => 'Qabul kunlari:',
                'phone_label' => 'Telefon:',
                'email_label' => 'Email:',
                'telegram_label' => 'Telegram:',
                'rector_bot_label' => 'Rektor Telegram boti:',
                'structure_title' => 'Maʼmuriyat',
            ],
            'ru' => [
                'home_tag' => 'Руководство университета',
                'home_title' => 'Исполнительная администрация БГТУ',
                'reception_label' => 'Дни приема:',
                'phone_label' => 'Телефон:',
                'email_label' => 'Email:',
                'telegram_label' => 'Telegram:',
                'rector_bot_label' => 'Telegram-бот ректора:',
                'structure_title' => 'Администрация',
            ],
            'ar' => [
                'home_tag' => 'قيادة الجامعة',
                'home_title' => 'الإدارة التنفيذية لجامعة بخارى التقنية الحكومية',
                'reception_label' => 'أيام الاستقبال:',
                'phone_label' => 'الهاتف:',
                'email_label' => 'البريد الإلكتروني:',
                'telegram_label' => 'تيليجرام:',
                'rector_bot_label' => 'بوت تيليجرام الخاص برئيس الجامعة:',
                'structure_title' => 'الإدارة',
            ],
        ];

        foreach ($settingTranslations as $locale => $translation) {
            $setting->translations()->updateOrCreate(['locale' => $locale], $translation);
        }

        foreach ($this->profiles() as $profileData) {
            $translations = $profileData['translations'];
            unset($profileData['translations']);

            $profile = AdministrationProfile::updateOrCreate(
                ['slug' => $profileData['slug']],
                $profileData
            );

            foreach ($translations as $locale => $translation) {
                $profile->translations()->updateOrCreate(['locale' => $locale], $translation);
            }
        }

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    private function profiles(): array
    {
        $path = database_path('data/administration-cms.json');
        $data = json_decode(file_get_contents($path), true);

        return $data['profiles'] ?? [];
    }
}


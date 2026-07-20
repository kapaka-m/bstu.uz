<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'site_name',
                'value' => 'BSTU International',
                'type' => 'text',
                'group' => 'general',
                'is_public' => true,
            ],
            [
                'key' => 'contact_email',
                'value' => 'international@bstu.uz',
                'type' => 'email',
                'group' => 'contact',
                'is_public' => true,
            ],
            [
                'key' => 'contact_phone',
                'value' => '+998 65 223 28 83',
                'type' => 'text',
                'group' => 'contact',
                'is_public' => true,
            ],
            [
                'key' => 'address',
                'value' => 'M. Iqbol Street 12, Bukhara, Uzbekistan',
                'type' => 'text',
                'group' => 'contact',
                'is_public' => true,
            ],
            [
                'key' => 'facebook_url',
                'value' => 'https://facebook.com/bstu.uz',
                'type' => 'url',
                'group' => 'social',
                'is_public' => true,
            ],
            [
                'key' => 'telegram_url',
                'value' => 'https://t.me/bstu_uz',
                'type' => 'url',
                'group' => 'social',
                'is_public' => true,
            ],
            [
                'key' => 'instagram_url',
                'value' => 'https://instagram.com/bstu_uz',
                'type' => 'url',
                'group' => 'social',
                'is_public' => true,
            ],
            [
                'key' => 'youtube_url',
                'value' => 'https://youtube.com/bstu_uz',
                'type' => 'url',
                'group' => 'social',
                'is_public' => true,
            ],
            [
                'key' => 'admission_email',
                'value' => 'admission@bstu.uz',
                'type' => 'email',
                'group' => 'admission',
                'is_public' => true,
            ],
            [
                'key' => 'call_center',
                'value' => '+998 65 223 28 83',
                'type' => 'text',
                'group' => 'contact',
                'is_public' => true,
            ],
            [
                'key' => 'rector_email',
                'value' => 'rector@bstu.uz',
                'type' => 'email',
                'group' => 'general',
                'is_public' => false,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}

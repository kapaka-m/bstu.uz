<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\Media;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

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
                'key' => 'site_meta_description',
                'value' => 'Bukhara State Technical University international admissions, academic programs, student services, and public university information.',
                'type' => 'text',
                'group' => 'general',
                'is_public' => true,
            ],
            [
                'key' => 'site_meta_keywords',
                'value' => 'Bukhara State Technical University, BSTU, international admissions, academic programs, Uzbekistan education',
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
            [
                'key' => 'branding_logo_default',
                'value' => 'cms/branding/bstu.png',
                'type' => 'media',
                'group' => 'branding',
                'is_public' => true,
            ],
            [
                'key' => 'branding_logo_en',
                'value' => 'cms/branding/bstu-en.png',
                'type' => 'media',
                'group' => 'branding',
                'is_public' => true,
            ],
            [
                'key' => 'branding_logo_ru',
                'value' => 'cms/branding/bstu-ru.png',
                'type' => 'media',
                'group' => 'branding',
                'is_public' => true,
            ],
            [
                'key' => 'branding_logo_ar',
                'value' => 'cms/branding/bstu-ar.png',
                'type' => 'media',
                'group' => 'branding',
                'is_public' => true,
            ],
            [
                'key' => 'branding_favicon_ico',
                'value' => 'cms/branding/favicon.ico',
                'type' => 'media',
                'group' => 'branding',
                'is_public' => true,
            ],
            [
                'key' => 'branding_favicon_png',
                'value' => 'cms/branding/favicon.png',
                'type' => 'media',
                'group' => 'branding',
                'is_public' => true,
            ],
            [
                'key' => 'branding_favicon_32',
                'value' => 'cms/branding/favicon-32x32.png',
                'type' => 'media',
                'group' => 'branding',
                'is_public' => true,
            ],
            [
                'key' => 'branding_favicon_16',
                'value' => 'cms/branding/favicon-16x16.png',
                'type' => 'media',
                'group' => 'branding',
                'is_public' => true,
            ],
            [
                'key' => 'branding_apple_touch_icon',
                'value' => 'cms/branding/apple-touch-icon.png',
                'type' => 'media',
                'group' => 'branding',
                'is_public' => true,
            ],
            [
                'key' => 'branding_android_chrome_512',
                'value' => 'cms/branding/android-chrome-512x512.png',
                'type' => 'media',
                'group' => 'branding',
                'is_public' => true,
            ],
            [
                'key' => 'branding_android_chrome_192',
                'value' => 'cms/branding/android-chrome-192x192.png',
                'type' => 'media',
                'group' => 'branding',
                'is_public' => true,
            ],
            [
                'key' => 'home_hero_background_image',
                'value' => 'cms/home/hero/hero-bg.png',
                'type' => 'media',
                'group' => 'home',
                'is_public' => true,
            ],
            [
                'key' => 'home_hero_main_image',
                'value' => 'cms/home/hero/hero-university.jpg',
                'type' => 'media',
                'group' => 'home',
                'is_public' => true,
            ],
        ];

        foreach ($settings as $setting) {
            if (str_starts_with($setting['key'], 'branding_') || str_starts_with($setting['key'], 'home_hero_')) {
                Setting::firstOrCreate(['key' => $setting['key']], $setting);

                continue;
            }

            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $brandingAssets = [
            'branding.favicon.ico' => ['path' => 'cms/branding/favicon.ico', 'filename' => 'favicon.ico', 'mime' => 'image/x-icon'],
            'branding.favicon.png' => ['path' => 'cms/branding/favicon.png', 'filename' => 'favicon.png', 'mime' => 'image/png'],
            'branding.favicon.32' => ['path' => 'cms/branding/favicon-32x32.png', 'filename' => 'favicon-32x32.png', 'mime' => 'image/png'],
            'branding.favicon.16' => ['path' => 'cms/branding/favicon-16x16.png', 'filename' => 'favicon-16x16.png', 'mime' => 'image/png'],
            'branding.logo.default' => ['path' => 'cms/branding/bstu.png', 'filename' => 'bstu.png', 'mime' => 'image/png'],
            'branding.logo.en' => ['path' => 'cms/branding/bstu-en.png', 'filename' => 'bstu-en.png', 'mime' => 'image/png'],
            'branding.logo.ru' => ['path' => 'cms/branding/bstu-ru.png', 'filename' => 'bstu-ru.png', 'mime' => 'image/png'],
            'branding.logo.ar' => ['path' => 'cms/branding/bstu-ar.png', 'filename' => 'bstu-ar.png', 'mime' => 'image/png'],
            'branding.apple_touch_icon' => ['path' => 'cms/branding/apple-touch-icon.png', 'filename' => 'apple-touch-icon.png', 'mime' => 'image/png'],
            'branding.android_chrome.512' => ['path' => 'cms/branding/android-chrome-512x512.png', 'filename' => 'android-chrome-512x512.png', 'mime' => 'image/png'],
            'branding.android_chrome.192' => ['path' => 'cms/branding/android-chrome-192x192.png', 'filename' => 'android-chrome-192x192.png', 'mime' => 'image/png'],
            'home.hero.background' => ['path' => 'cms/home/hero/hero-bg.png', 'filename' => 'hero-bg.png', 'mime' => 'image/png'],
            'home.hero.main' => ['path' => 'cms/home/hero/hero-university.jpg', 'filename' => 'hero-university.jpg', 'mime' => 'image/jpeg'],
        ];

        foreach ($brandingAssets as $altKey => $asset) {
            Media::updateOrCreate(
                ['alt_key' => $altKey],
                [
                    'disk' => 'public',
                    'path' => $asset['path'],
                    'filename' => $asset['filename'],
                    'title' => 'Branding: '.$asset['filename'],
                    'alt_text' => 'BSTU branding asset',
                    'type' => 'image',
                    'mime_type' => $asset['mime'],
                    'size' => Storage::disk('public')->exists($asset['path'])
                        ? Storage::disk('public')->size($asset['path'])
                        : 0,
                    'is_public' => true,
                ]
            );
        }
    }
}

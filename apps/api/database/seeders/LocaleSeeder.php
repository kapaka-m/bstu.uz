<?php

namespace Database\Seeders;

use App\Models\Locale;
use Illuminate\Database\Seeder;

class LocaleSeeder extends Seeder
{
    public function run(): void
    {
        Locale::updateOrCreate(['code' => 'en'], [
            'code' => 'en',
            'name' => 'English',
            'native_name' => 'English',
            'direction' => 'ltr',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Locale::updateOrCreate(['code' => 'uz'], [
            'code' => 'uz',
            'name' => 'Uzbek',
            'native_name' => 'O‘zbekcha',
            'direction' => 'ltr',
            'is_active' => true,
            'sort_order' => 2,
        ]);

        Locale::updateOrCreate(['code' => 'ru'], [
            'code' => 'ru',
            'name' => 'Russian',
            'native_name' => 'Русский',
            'direction' => 'ltr',
            'is_active' => true,
            'sort_order' => 3,
        ]);

        Locale::updateOrCreate(['code' => 'ar'], [
            'code' => 'ar',
            'name' => 'Arabic',
            'native_name' => 'العربية',
            'direction' => 'rtl',
            'is_active' => true,
            'sort_order' => 4,
        ]);
    }
}

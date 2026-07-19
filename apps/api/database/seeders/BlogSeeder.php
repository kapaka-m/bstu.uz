<?php

namespace Database\Seeders;

use App\Models\BlogSetting;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    protected array $locales = ['en', 'uz', 'ru', 'ar'];

    public function run(): void
    {
        $this->seedSettings();
    }

    protected function seedSettings(): void
    {
        $setting = BlogSetting::updateOrCreate(
            ['key' => 'main'],
            [
                'home_limit' => 3,
                'recent_limit' => 5,
                'tags' => [],
                'is_active' => true,
            ]
        );

        $fields = [
            'home_tag' => '',
            'home_title' => '',
            'view_all_label' => '',
            'read_more_label' => '',
            'search_title' => '',
            'search_placeholder' => '',
            'categories_title' => '',
            'recent_title' => '',
            'tags_title' => '',
            'all_blog_label' => '',
            'loading_label' => '',
            'no_results_label' => '',
            'clear_filters_label' => '',
            'back_to_blog_label' => '',
            'comments_label' => '',
            'reply_label' => '',
            'form_title' => '',
            'form_name_label' => '',
            'form_email_label' => '',
            'form_comment_label' => '',
            'form_submit_label' => '',
        ];

        foreach ($this->locales as $locale) {
            $setting->translations()->updateOrCreate(
                ['locale' => $locale],
                $fields
            );
        }
    }

}

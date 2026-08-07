<?php

namespace Database\Seeders;

use App\Models\Locale;
use App\Models\WebFooter;
use Illuminate\Database\Seeder;

class WebFooterSeeder extends Seeder
{
    private const EXCLUDED_USEFUL_LINK_KEYS = [
        'announcements',
        'news',
        'blog',
    ];

    public function run(): void
    {
        $footer = WebFooter::firstOrCreate(
            ['key' => 'main'],
            [
                'admissions_apply_url' => '/apply',
                'phone' => '+998 65 224 64 35',
                'email' => 'info@bstu.uz',
                'copyright_year' => 2026,
                'is_active' => true,
            ],
        );

        $updates = [];

        $updates['useful_links'] = $this->mergeLinksByKey(
            $this->withoutLinksByKey($footer->useful_links ?: [], self::EXCLUDED_USEFUL_LINK_KEYS),
            [
                ['key' => 'home', 'url' => '/'],
                ['key' => 'about', 'url' => '/about'],
                ['key' => 'programs', 'url' => '/programs'],
                ['key' => 'services', 'url' => '/services'],
                ['key' => 'contact', 'url' => '/contact'],
            ],
        );

        $updates['faculty_links'] = $this->mergeLinksByKey($footer->faculty_links ?: [], [
            ['key' => 'engineering', 'url' => '/faculty/faculty-of-engineering'],
            ['key' => 'technology', 'url' => '/faculty/faculty-of-technology'],
            ['key' => 'service', 'url' => '/faculty/faculty-of-service-and-digitalization'],
            ['key' => 'natural', 'url' => '/faculty/faculty-of-natural-resources-management'],
        ]);

        $updates['social_links'] = $this->mergeLinksByKey($footer->social_links ?: [], [
            ['key' => 'website', 'label' => 'Website', 'url' => 'https://www.bstu.uz/'],
            ['key' => 'telegram', 'label' => 'Telegram', 'url' => 'https://t.me/bdtu_uz_rasmiy'],
            ['key' => 'instagram', 'label' => 'Instagram', 'url' => 'https://www.instagram.com/bdtu.uz'],
            ['key' => 'youtube', 'label' => 'YouTube', 'url' => 'https://www.youtube.com/channel/UCzQe2GCCLZqa-aUItTAas2Q'],
            ['key' => 'facebook', 'label' => 'Facebook', 'url' => 'https://www.facebook.com/BDTU.UZ'],
        ]);

        if ($updates) {
            $footer->update($updates);
        }

        $this->mergeFooterLabels($footer->fresh());
    }

    private function mergeFooterLabels(WebFooter $footer): void
    {
        $usefulLabels = [
            'en' => [
                'home' => 'Home',
                'about' => 'About',
                'programs' => 'Programs',
                'services' => 'Services',
                'contact' => 'Contact',
            ],
            'uz' => [
                'home' => 'Bosh sahifa',
                'about' => 'Biz haqimizda',
                'programs' => 'Dasturlar',
                'services' => 'Xizmatlar',
                'contact' => 'Aloqa',
            ],
            'ru' => [
                'home' => 'Главная',
                'about' => 'О нас',
                'programs' => 'Программы',
                'services' => 'Услуги',
                'contact' => 'Контакт',
            ],
            'ar' => [
                'home' => 'الرئيسية',
                'about' => 'من نحن',
                'programs' => 'البرامج',
                'services' => 'الخدمات',
                'contact' => 'تواصل معنا',
            ],
        ];

        $facultyLabels = [
            'en' => [
                'engineering' => 'Faculty of Engineering',
                'technology' => 'Faculty of Technology',
                'service' => 'Faculty of Service and Digitalization',
                'natural' => 'Faculty of Natural Resources Management',
            ],
            'uz' => [
                'engineering' => 'Muhandislik fakulteti',
                'technology' => 'Texnologiya fakulteti',
                'service' => 'Xizmat ko‘rsatish va raqamlashtirish fakulteti',
                'natural' => 'Tabiiy resurslarni boshqarish fakulteti',
            ],
            'ru' => [
                'engineering' => 'Инженерный факультет',
                'technology' => 'Факультет технологий',
                'service' => 'Факультет сервиса и цифровизации',
                'natural' => 'Факультет управления природными ресурсами',
            ],
            'ar' => [
                'engineering' => 'كلية الهندسة',
                'technology' => 'كلية التكنولوجيا',
                'service' => 'كلية الخدمات والرقمنة',
                'natural' => 'كلية إدارة الموارد الطبيعية',
            ],
        ];

        $sectionTitles = [
            'en' => ['useful_links_title' => 'Useful Links', 'faculties_title' => 'Faculties'],
            'uz' => ['useful_links_title' => 'Foydali havolalar', 'faculties_title' => 'Fakultetlar'],
            'ru' => ['useful_links_title' => 'Полезные ссылки', 'faculties_title' => 'Факультеты'],
            'ar' => ['useful_links_title' => 'روابط مفيدة', 'faculties_title' => 'الكليات'],
        ];

        $contentDefaults = [
            'en' => [
                'admissions_badge' => 'Admissions 2026/2027 Open',
                'admissions_heading' => 'Shape Your Future in Engineering & Technology',
                'admissions_description' => 'Join Bukhara State Technical University and gain access to advanced laboratories, industry-partnered curriculums, and globally recognized degrees.',
                'admissions_button_label' => 'Apply Online',
                'newsletter_title' => 'Our Newsletter',
                'newsletter_description' => 'Subscribe to receive the latest updates, event announcements, and academic news.',
                'newsletter_placeholder' => 'Your email address',
                'newsletter_success_message' => 'Thank you for subscribing.',
            ],
            'uz' => [
                'admissions_badge' => '2026/2027 qabul ochiq',
                'admissions_heading' => 'Muhandislik va texnologiyada kelajagingizni yarating',
                'admissions_description' => 'Buxoro davlat texnika universitetiga qo‘shiling va ilg‘or laboratoriyalar, sanoat hamkorligidagi o‘quv dasturlari hamda xalqaro tan olingan diplomlarga ega bo‘ling.',
                'admissions_button_label' => 'Onlayn ariza topshirish',
                'newsletter_title' => 'Bizning yangiliklarimiz',
                'newsletter_description' => 'Eng so‘nggi yangiliklar, tadbir eʼlonlari va akademik xabarlarni olish uchun obuna bo‘ling.',
                'newsletter_placeholder' => 'Email manzilingiz',
                'newsletter_success_message' => 'Obunangiz uchun rahmat.',
            ],
            'ru' => [
                'admissions_badge' => 'Прием 2026/2027 открыт',
                'admissions_heading' => 'Создайте свое будущее в инженерии и технологиях',
                'admissions_description' => 'Поступайте в Бухарский государственный технический университет и получите доступ к современным лабораториям, учебным программам с индустриальными партнерами и международно признанным дипломам.',
                'admissions_button_label' => 'Подать заявку онлайн',
                'newsletter_title' => 'Наша рассылка',
                'newsletter_description' => 'Подпишитесь, чтобы получать последние новости, объявления о событиях и академические обновления.',
                'newsletter_placeholder' => 'Ваш email адрес',
                'newsletter_success_message' => 'Спасибо за подписку.',
            ],
            'ar' => [
                'admissions_badge' => 'القبول 2026/2027 مفتوح',
                'admissions_heading' => 'اصنع مستقبلك في الهندسة والتكنولوجيا',
                'admissions_description' => 'انضم إلى جامعة بخارى التقنية الحكومية واحصل على مختبرات متقدمة، ومناهج مرتبطة بالصناعة، وشهادات معترف بها عالميا.',
                'admissions_button_label' => 'قدّم الآن',
                'newsletter_title' => 'النشرة الإخبارية',
                'newsletter_description' => 'اشترك لتصلك آخر التحديثات وإعلانات الفعاليات والأخبار الأكاديمية.',
                'newsletter_placeholder' => 'عنوان بريدك الإلكتروني',
                'newsletter_success_message' => 'شكرا لاشتراكك.',
            ],
        ];

        $locales = Locale::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('code');

        foreach ($locales as $locale) {
            if (! isset($usefulLabels[$locale], $facultyLabels[$locale])) {
                continue;
            }

            $translation = $footer->translations()->firstOrCreate(['locale' => $locale]);
            $usefulKeys = array_flip(array_column($footer->useful_links ?: [], 'key'));
            $facultyKeys = array_flip(array_column($footer->faculty_links ?: [], 'key'));

            $translation->update([
                'admissions_badge' => $translation->admissions_badge ?: $contentDefaults[$locale]['admissions_badge'],
                'admissions_heading' => $translation->admissions_heading ?: $contentDefaults[$locale]['admissions_heading'],
                'admissions_description' => $translation->admissions_description ?: $contentDefaults[$locale]['admissions_description'],
                'admissions_button_label' => $translation->admissions_button_label ?: $contentDefaults[$locale]['admissions_button_label'],
                'newsletter_title' => $translation->newsletter_title ?: $contentDefaults[$locale]['newsletter_title'],
                'newsletter_description' => $translation->newsletter_description ?: $contentDefaults[$locale]['newsletter_description'],
                'newsletter_placeholder' => $translation->newsletter_placeholder ?: $contentDefaults[$locale]['newsletter_placeholder'],
                'newsletter_success_message' => $translation->newsletter_success_message ?: $contentDefaults[$locale]['newsletter_success_message'],
                'useful_links_title' => $translation->useful_links_title ?: $sectionTitles[$locale]['useful_links_title'],
                'faculties_title' => $translation->faculties_title ?: $sectionTitles[$locale]['faculties_title'],
                'useful_link_labels' => array_intersect_key(
                    array_merge($usefulLabels[$locale], $translation->useful_link_labels ?: []),
                    $usefulKeys,
                ),
                'faculty_link_labels' => array_intersect_key(
                    array_merge($facultyLabels[$locale], $translation->faculty_link_labels ?: []),
                    $facultyKeys,
                ),
            ]);
        }
    }

    private function mergeLinksByKey(array $existing, array $defaults): array
    {
        $merged = [];

        foreach ($existing as $link) {
            if (! is_array($link) || empty($link['key'])) {
                continue;
            }

            $merged[$link['key']] = $link;
        }

        foreach ($defaults as $link) {
            $key = $link['key'];
            $merged[$key] = array_merge($link, $merged[$key] ?? []);
        }

        return array_values($merged);
    }

    private function withoutLinksByKey(array $links, array $excludedKeys): array
    {
        return array_values(array_filter($links, static function ($link) use ($excludedKeys): bool {
            return is_array($link)
                && ! empty($link['key'])
                && ! in_array($link['key'], $excludedKeys, true);
        }));
    }
}

<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\MenuItemTranslation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class MenuSeeder extends Seeder
{
    private array $translations = [];

    public function run(): void
    {
        $filePath = database_path('data/translations.json');
        if (file_exists($filePath)) {
            $this->translations = json_decode(file_get_contents($filePath), true) ?: [];
        }

        $headerMenu = Menu::updateOrCreate(
            ['key' => 'main_header'],
            ['location' => 'header', 'is_active' => true]
        );

        $headerMenu->items()->delete();

        foreach ($this->headerItems() as $index => $itemData) {
            $this->createItem($headerMenu, $itemData, null, $index + 1);
        }
    }

    private function headerItems(): array
    {
        return [
            $this->link('/', 'nav.home', ['en' => 'Home', 'uz' => 'Bosh sahifa', 'ru' => 'Главная', 'ar' => 'الرئيسية']),
            $this->link('/about', 'nav.aboutUs', ['en' => 'About', 'uz' => 'Biz haqimizda', 'ru' => 'О нас', 'ar' => 'عن الجامعة']),
            [
                'route_name' => 'media',
                'labels' => ['en' => 'Media Center', 'uz' => 'Media markazi', 'ru' => 'Медиацентр', 'ar' => 'المركز الإعلامي'],
                'children' => [
                    $this->link('/announcements', 'nav.announcements', ['en' => 'Announcements', 'uz' => 'E’lonlar', 'ru' => 'Объявления', 'ar' => 'الإعلانات']),
                    $this->link('/news', 'nav.news', ['en' => 'News', 'uz' => 'Yangiliklar', 'ru' => 'Новости', 'ar' => 'الأخبار']),
                    $this->link('/blog', 'nav.blog', ['en' => 'Blog', 'uz' => 'Blog', 'ru' => 'Блог', 'ar' => 'المدونة']),
                    $this->link('/video-bdtu', 'nav.videoBdtu', ['en' => 'Video BDTU', 'uz' => 'Video BuxDTU', 'ru' => 'Видео BDTU', 'ar' => 'فيديو BDTU']),
                    $this->link('/green-campus', 'nav.greenCampus', ['en' => 'Green Campus', 'uz' => 'Yashil kampus', 'ru' => 'Зеленый кампус', 'ar' => 'الحرم الأخضر']),
                ],
            ],
            [
                'route_name' => 'faculties',
                'labels' => $this->pathLabels('nav.faculties', ['en' => 'Faculties', 'uz' => 'Fakultetlar', 'ru' => 'Факультеты', 'ar' => 'الكليات']),
                'children' => $this->facultyItems(),
            ],
            [
                'route_name' => 'structure',
                'labels' => $this->pathLabels('nav.structure', ['en' => 'Structure', 'uz' => 'Tuzilma', 'ru' => 'Структура', 'ar' => 'الهيكل']),
                'children' => [
                    [
                        'route_name' => 'group',
                        'labels' => ['en' => 'Administration', 'uz' => 'Ma’muriyat', 'ru' => 'Администрация', 'ar' => 'الإدارة'],
                        'children' => $this->administrationItems(),
                    ],
                    [
                        'route_name' => 'group',
                        'labels' => ['en' => 'Centres and departments', 'uz' => 'Markazlar va bo‘limlar', 'ru' => 'Центры и отделы', 'ar' => 'المراكز والإدارات'],
                        'children' => $this->centerItems(),
                    ],
                ],
            ],
            $this->link('/services', 'nav.services', ['en' => 'Services', 'uz' => 'Xizmatlar', 'ru' => 'Сервисы', 'ar' => 'الخدمات']),
            $this->link('/contact', 'nav.contact', ['en' => 'Contact', 'uz' => 'Aloqa', 'ru' => 'Контакты', 'ar' => 'اتصل بنا']),
            $this->action('/login', 'login', 'nav.login', ['en' => 'Login', 'uz' => 'Tizimga kirish', 'ru' => 'Войти', 'ar' => 'تسجيل الدخول']),
            $this->action('/apply', 'apply', 'nav.applyOnline', ['en' => 'Apply Online', 'uz' => 'Onlayn ariza', 'ru' => 'Подать онлайн', 'ar' => 'التقديم عبر الإنترنت']),
        ];
    }

    private function facultyItems(): array
    {
        return [
            $this->groupLink('/faculty/faculty-of-engineering', null, [
                'en' => 'Faculty of Engineering',
                'uz' => 'Muhandislik fakulteti',
                'ru' => 'Факультет инженерии',
                'ar' => 'كلية الهندسة',
            ], [
                $this->department('electrical-power-engineering'),
                $this->department('architecture'),
                $this->department('civil-engineering'),
                $this->department('light-industry-engineering-and-design'),
                $this->department('mechanics-engineering-graphics', 'mechanics-and-engineering-graphics'),
                $this->department('technological-machines-equipment', 'technological-machines-and-equipment'),
            ]),
            $this->groupLink('/faculty/faculty-of-technology', null, [
                'en' => 'Faculty of Technology',
                'uz' => 'Texnologiya fakulteti',
                'ru' => 'Факультет технологии',
                'ar' => 'كلية التكنولوجيا',
            ], [
                $this->department('oil-gas-refining-technology', 'oil-and-gas-refining-technology'),
                $this->department('food-technology-service', 'food-technology-and-service'),
                $this->department('chemical-technology'),
                $this->department('agricultural-products-storage-oil-fat-technology'),
                $this->department('oil-gas-engineering-upstream-downstream', 'oil-and-gas-engineering-upstream-downstream'),
                $this->department('metrology-standardization-quality-control', 'metrology-standardization-and-quality-control'),
            ]),
            $this->groupLink('/faculty/faculty-of-natural-resources-management', null, [
                'en' => 'Faculty of Natural Resources Management',
                'uz' => 'Tabiiy resurslarni boshqarish fakulteti',
                'ru' => 'Факультет управления природными ресурсами',
                'ar' => 'كلية إدارة الموارد الطبيعية',
            ], [
                $this->department('irrigation-melioration'),
                $this->department('hydrotechnical-structures-pump-stations'),
                $this->department('agricultural-water-resources-engineering-technologies', 'agricultural-and-water-resources-engineering-technologies'),
                $this->department('land-resources-management-state-land-cadastres'),
                $this->department('industrial-ecology-hydrogeology'),
                $this->department('vehicle-engineering-automotive-transport-systems'),
            ]),
            $this->groupLink('/faculty/faculty-of-service-and-digitalization', null, [
                'en' => 'Faculty of Service and Digitalization',
                'uz' => 'Xizmat ko‘rsatish va raqamlashtirish fakulteti',
                'ru' => 'Факультет сервиса и цифровизации',
                'ar' => 'كلية الخدمات والرقمنة',
            ], [
                $this->department('technological-processes-production-automation'),
                $this->department('information-and-communication-technologies'),
                $this->department('economics-and-management'),
                $this->department('artificial-intelligence-digitalization', 'artificial-intelligence-and-digitalization'),
                $this->department('social-sciences-physical-culture', 'social-sciences-and-physical-culture'),
                $this->department('exact-sciences'),
            ]),
        ];
    }

    private function administrationItems(): array
    {
        return [
            $this->link('/profile/rector', null, ['en' => 'Rector of the University', 'uz' => 'Universitet rektori', 'ru' => 'Ректор университета', 'ar' => 'رئيسة الجامعة']),
            $this->link('/profile/vice-rector-youth', null, ['en' => 'First Vice Rector for Youth Affairs and Spiritual and Educational Work', 'uz' => 'Yoshlar masalalari va ma’naviy-ma’rifiy ishlar bo‘yicha birinchi prorektor', 'ru' => 'Первый проректор по делам молодежи и духовно-просветительской работе', 'ar' => 'النائب الأول للرئيس لشؤون الشباب والعمل الروحي والتربوي']),
            $this->link('/profile/vice-rector-research', null, ['en' => 'Vice Rector for Research and Innovation', 'uz' => 'Ilmiy ishlar va innovatsiyalar bo‘yicha prorektor', 'ru' => 'Проректор по научной работе и инновациям', 'ar' => 'نائب الرئيس للبحث والابتكار']),
            $this->link('/profile/vice-rector-academic', null, ['en' => 'Vice Rector for Academic Affairs', 'uz' => 'O‘quv ishlari bo‘yicha prorektor', 'ru' => 'Проректор по учебной работе', 'ar' => 'نائب الرئيس للشؤون الأكاديمية']),
            $this->link('/profile/vice-rector-finance', null, ['en' => 'Vice Rector for Finance and Economy', 'uz' => 'Moliyaviy-iqtisodiy ishlar bo‘yicha prorektor', 'ru' => 'Проректор по финансово-экономической работе', 'ar' => 'نائب الرئيس للشؤون المالية والاقتصادية']),
            $this->link('/profile/vice-rector-international', null, ['en' => 'Vice Rector for International Cooperation', 'uz' => 'Xalqaro hamkorlik bo‘yicha prorektor', 'ru' => 'Проректор по международному сотрудничеству', 'ar' => 'نائب الرئيس للتعاون الدولي']),
        ];
    }

    private function centerItems(): array
    {
        return collect([
            'digital-educational-technologies',
            'office-archive',
            'monitoring-internal-control',
            'personnel-department',
            'educational-quality-control',
            'trade-union-committee',
            'academic-affairs-management',
            'masters-department',
            'youth-spirituality-enlightenment',
            'gifted-students-research',
            'international-cooperation-department',
            'anti-corruption-compliance',
            'youth-union-organization',
            'civil-and-labor-protection',
            'legal-service',
            'scientific-research-department',
            'information-resource-center',
        ])->map(fn (string $slug) => $this->link("/center/{$slug}", "centers.{$slug}.name", [
            'en' => str($slug)->replace('-', ' ')->title()->toString(),
        ]))->all();
    }

    private function department(string $slug, ?string $translationSlug = null): array
    {
        return $this->link("/department/{$slug}", 'departments.'.($translationSlug ?: $slug).'.name', [
            'en' => 'Department of '.str($slug)->replace('-', ' ')->title()->toString(),
        ]);
    }

    private function groupLink(string $url, ?string $translationPath, array $fallbackLabels, array $children): array
    {
        return [
            'route_name' => 'group',
            'url' => $url,
            'labels' => $translationPath ? $this->pathLabels($translationPath, $fallbackLabels) : $this->completeLabels($fallbackLabels),
            'children' => $children,
        ];
    }

    private function link(string $url, ?string $translationPath, array $fallbackLabels): array
    {
        return [
            'route_name' => 'link',
            'url' => $url,
            'labels' => $translationPath ? $this->pathLabels($translationPath, $fallbackLabels) : $this->completeLabels($fallbackLabels),
        ];
    }

    private function action(string $url, string $icon, ?string $translationPath, array $fallbackLabels): array
    {
        return [
            'route_name' => 'action',
            'url' => $url,
            'icon' => $icon,
            'labels' => $translationPath ? $this->pathLabels($translationPath, $fallbackLabels) : $this->completeLabels($fallbackLabels),
        ];
    }

    private function pathLabels(string $path, array $fallbackLabels): array
    {
        $labels = [];
        foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
            $labels[$locale] = Arr::get($this->translations, "{$locale}.{$path}")
                ?: ($fallbackLabels[$locale] ?? $fallbackLabels['en'] ?? $path);
        }

        return $labels;
    }

    private function completeLabels(array $labels): array
    {
        $fallback = $labels['en'] ?? reset($labels) ?: 'Menu Item';

        return [
            'en' => $labels['en'] ?? $fallback,
            'uz' => $labels['uz'] ?? $fallback,
            'ru' => $labels['ru'] ?? $fallback,
            'ar' => $labels['ar'] ?? $fallback,
        ];
    }

    private function createItem(Menu $menu, array $itemData, ?int $parentId, int $sortOrder): MenuItem
    {
        $routeName = $itemData['route_name'] ?? 'link';
        $item = MenuItem::create([
            'menu_id' => $menu->id,
            'parent_id' => $parentId,
            'route_name' => $routeName,
            'url' => in_array($routeName, ['link', 'group', 'action'], true) ? ($itemData['url'] ?? null) : null,
            'icon' => $itemData['icon'] ?? null,
            'sort_order' => $sortOrder,
            'is_active' => true,
        ]);

        foreach ($this->completeLabels($itemData['labels'] ?? []) as $locale => $label) {
            MenuItemTranslation::updateOrCreate(
                ['menu_item_id' => $item->id, 'locale' => $locale],
                ['label' => $label]
            );
        }

        foreach (($itemData['children'] ?? []) as $childIndex => $childData) {
            $this->createItem($menu, $childData, $item->id, $childIndex + 1);
        }

        return $item;
    }
}

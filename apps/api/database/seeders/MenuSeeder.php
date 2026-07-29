<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\MenuItemTranslation;
use App\Models\AdministrationProfile;
use App\Models\Locale;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class MenuSeeder extends Seeder
{
    private array $translations = [];
    private array $locales = [];

    public function run(): void
    {
        $filePath = database_path('data/translations.json');
        if (file_exists($filePath)) {
            $this->translations = json_decode(file_get_contents($filePath), true) ?: [];
        }
        $this->locales = $this->localeCodes();

        $headerMenu = Menu::firstOrCreate(
            ['key' => 'main_header'],
            ['location' => 'header', 'is_active' => true]
        );

        if ($headerMenu->items()->exists()) {
            return;
        }

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
        return AdministrationProfile::query()
            ->with('translations')
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (AdministrationProfile $profile) {
                $translations = $profile->translations->keyBy('locale');
                $labels = [];

                foreach ($this->locales as $locale) {
                    $translation = $translations->get($locale);
                    $labels[$locale] = $translation?->position ?: $translation?->name ?: $profile->slug;
                }

                return $this->link("/profile/{$profile->slug}", null, $labels);
            })
            ->all();
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

    private function groupLink(string $url, ?string $translationPath, array $seedLabels, array $children): array
    {
        return [
            'route_name' => 'group',
            'url' => $url,
            'labels' => $translationPath ? $this->pathLabels($translationPath, $seedLabels) : $this->completeLabels($seedLabels),
            'children' => $children,
        ];
    }

    private function link(string $url, ?string $translationPath, array $seedLabels): array
    {
        return [
            'route_name' => 'link',
            'url' => $url,
            'labels' => $translationPath ? $this->pathLabels($translationPath, $seedLabels) : $this->completeLabels($seedLabels),
        ];
    }

    private function action(string $url, string $icon, ?string $translationPath, array $seedLabels): array
    {
        return [
            'route_name' => 'action',
            'url' => $url,
            'icon' => $icon,
            'labels' => $translationPath ? $this->pathLabels($translationPath, $seedLabels) : $this->completeLabels($seedLabels),
        ];
    }

    private function pathLabels(string $path, array $seedLabels): array
    {
        $labels = [];
        foreach ($this->locales as $locale) {
            $val = null;

            if ($locale === 'uz') {
                $val = Arr::get($this->translations, $path);

                if ($val === null && str_ends_with($path, '.name')) {
                    $parentVal = Arr::get($this->translations, substr($path, 0, -5));
                    if (is_string($parentVal)) {
                        $val = $parentVal;
                    }
                }

                if ($val === null) {
                    $val = Arr::get($this->translations, "uz.{$path}");
                }
            } else {
                $val = Arr::get($this->translations, "{$locale}.{$path}");
            }

            if ($val === null && str_ends_with($path, '.name')) {
                $prefix = $locale === 'uz' ? '' : "{$locale}.";
                $parentVal = Arr::get($this->translations, $prefix . substr($path, 0, -5));
                if (is_string($parentVal)) {
                    $val = $parentVal;
                }
            }

            if (is_array($val)) {
                $val = $val['name'] ?? null;
            }

            if ($val !== null || isset($seedLabels[$locale])) {
                $labels[$locale] = $val ?: $seedLabels[$locale];
            }
        }

        return $labels;
    }

    private function completeLabels(array $labels): array
    {
        return array_intersect_key($labels, array_flip($this->locales));
    }

    private function localeCodes(): array
    {
        $locales = Locale::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('code')
            ->all();

        if ($locales !== []) {
            return $locales;
        }

        return array_values(array_filter(
            array_keys($this->translations),
            fn (string $key) => is_array($this->translations[$key] ?? null)
        ));
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
            MenuItemTranslation::firstOrCreate(
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

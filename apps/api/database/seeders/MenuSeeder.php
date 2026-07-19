<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\MenuItemTranslation;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('data/translations.json');
        if (! file_exists($filePath)) {
            return;
        }

        $translations = json_decode(file_get_contents($filePath), true);

        // Header Menu
        $headerMenu = Menu::updateOrCreate(
            ['key' => 'main_header'],
            [
                'location' => 'header',
                'is_active' => true,
            ]
        );

        $items = [
            [
                'route_name' => 'home',
                'url' => '/',
                'sort_order' => 1,
                'key' => 'home',
            ],
            [
                'route_name' => 'about',
                'url' => '/about',
                'sort_order' => 2,
                'key' => 'aboutUs',
            ],
            [
                'route_name' => 'structure',
                'url' => '/structure',
                'sort_order' => 3,
                'key' => 'structure',
            ],
            [
                'route_name' => 'programs',
                'url' => '/programs',
                'sort_order' => 4,
                'key' => 'programs',
            ],
            [
                'route_name' => 'admission',
                'url' => '/admission',
                'sort_order' => 5,
                'key' => 'admission',
            ],
            [
                'route_name' => 'green-campus',
                'url' => '/green-campus',
                'sort_order' => 6,
                'key' => 'greenCampus',
            ],
            [
                'route_name' => 'dormitory',
                'url' => '/dormitory',
                'sort_order' => 7,
                'key' => 'dormitory',
            ],
            [
                'route_name' => 'contact',
                'url' => '/contact',
                'sort_order' => 8,
                'key' => 'contact',
            ],
        ];

        foreach ($items as $itemData) {
            $item = MenuItem::updateOrCreate(
                [
                    'menu_id' => $headerMenu->id,
                    'route_name' => $itemData['route_name'],
                ],
                [
                    'url' => $itemData['url'],
                    'sort_order' => $itemData['sort_order'],
                    'is_active' => true,
                ]
            );

            foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
                $label = $translations[$locale]['nav'][$itemData['key']] ?? ($translations['en']['nav'][$itemData['key']] ?? ucfirst($itemData['route_name']));

                MenuItemTranslation::updateOrCreate(
                    [
                        'menu_item_id' => $item->id,
                        'locale' => $locale,
                    ],
                    [
                        'label' => $label,
                    ]
                );
            }
        }
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->normalizeCenterContacts();
        $this->normalizeCenterTranslations();
        $this->syncHeaderCenterLabels();
    }

    public function down(): void
    {
        //
    }

    private function normalizeCenterContacts(): void
    {
        $contacts = [
            'digital-educational-technologies' => [
                'email' => 'digital@bstu.uz',
                'phone' => '+998 65 224 64 35 (Ext. 403)',
            ],
            'legal-service' => [
                'email' => 'legal@bstu.uz',
                'phone' => '+998 93 471 50 75',
            ],
            'academic-affairs-management' => [
                'phone' => '+998 90 744 01 79',
            ],
            'masters-department' => [
                'phone' => '+998 91 310 40 37',
            ],
            'international-cooperation-department' => [
                'phone' => '+998 65 223 51 41',
            ],
        ];

        foreach ($contacts as $slug => $values) {
            DB::table('university_centers')
                ->where('slug', $slug)
                ->update($values + ['updated_at' => now()]);
        }
    }

    private function normalizeCenterTranslations(): void
    {
        $labels = [
            'office-archive' => [
                'en' => 'Chancellery and Archival Department',
                'uz' => 'Devonxona va arxiv bo‘limi',
                'ru' => 'Канцелярия и архивный отдел',
                'ar' => 'قسم الديوان والأرشيف',
            ],
            'monitoring-internal-control' => [
                'en' => 'Monitoring and Internal Control Department',
                'uz' => 'Monitoring va ichki nazorat bo‘limi',
                'ru' => 'Отдел мониторинга и внутреннего контроля',
                'ar' => 'قسم المراقبة والرقابة الداخلية',
            ],
        ];

        foreach ($labels as $slug => $translations) {
            $centerId = DB::table('university_centers')->where('slug', $slug)->value('id');

            if (! $centerId) {
                continue;
            }

            foreach ($translations as $locale => $name) {
                DB::table('university_center_translations')->updateOrInsert(
                    ['university_center_id' => $centerId, 'locale' => $locale],
                    ['name' => $name, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        $youthCenterId = DB::table('university_centers')
            ->where('slug', 'youth-spirituality-enlightenment')
            ->value('id');

        if ($youthCenterId) {
            DB::table('university_center_translations')
                ->where('university_center_id', $youthCenterId)
                ->where(function ($query) {
                    $query->whereNull('head')
                        ->orWhere('head', '')
                        ->orWhere('head', '-');
                })
                ->update([
                    'head' => '',
                    'head_title' => '',
                    'office_hours' => '',
                    'head_description' => '',
                    'updated_at' => now(),
                ]);
        }
    }

    private function syncHeaderCenterLabels(): void
    {
        $menuId = DB::table('menus')
            ->whereIn('key', ['main_header', 'main'])
            ->where('location', 'header')
            ->value('id');

        if (! $menuId) {
            return;
        }

        foreach ($this->centerMenuItems() as $url => $item) {
            $menuItemId = DB::table('menu_items')
                ->where('menu_id', $menuId)
                ->where('url', $url)
                ->value('id');

            if (! $menuItemId) {
                continue;
            }

            DB::table('menu_items')->where('id', $menuItemId)->update([
                'route_name' => 'link',
                'sort_order' => $item['sort_order'],
                'is_active' => true,
                'updated_at' => now(),
            ]);

            foreach ($item['labels'] as $locale => $label) {
                DB::table('menu_item_translations')->updateOrInsert(
                    ['menu_item_id' => $menuItemId, 'locale' => $locale],
                    ['label' => $label, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    private function centerMenuItems(): array
    {
        return [
            '/center/digital-educational-technologies' => [
                'sort_order' => 1,
                'labels' => [
                    'en' => 'Digital Educational Technologies Centre',
                    'uz' => 'Raqamli ta’lim texnologiyalari markazi',
                    'ru' => 'Центр цифровых образовательных технологий',
                    'ar' => 'مركز تقنيات التعليم الرقمي',
                ],
            ],
            '/center/office-archive' => [
                'sort_order' => 2,
                'labels' => [
                    'en' => 'Chancellery and Archival Department',
                    'uz' => 'Devonxona va arxiv bo‘limi',
                    'ru' => 'Канцелярия и архивный отдел',
                    'ar' => 'قسم الديوان والأرشيف',
                ],
            ],
            '/center/monitoring-internal-control' => [
                'sort_order' => 3,
                'labels' => [
                    'en' => 'Monitoring and Internal Control Department',
                    'uz' => 'Monitoring va ichki nazorat bo‘limi',
                    'ru' => 'Отдел мониторинга и внутреннего контроля',
                    'ar' => 'قسم المراقبة والرقابة الداخلية',
                ],
            ],
        ];
    }
};

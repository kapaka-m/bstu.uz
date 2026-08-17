<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $menuId = DB::table('menus')
            ->whereIn('key', ['main_header', 'main'])
            ->where('location', 'header')
            ->value('id');

        if (! $menuId) {
            return;
        }

        $facultyItem = DB::table('menu_items')
            ->where('menu_id', $menuId)
            ->where('url', '/faculty/faculty-of-service-and-digitalization')
            ->first();

        if (! $facultyItem) {
            return;
        }

        $urls = array_keys($this->items());

        DB::table('menu_items')
            ->where('menu_id', $menuId)
            ->where('parent_id', $facultyItem->id)
            ->whereNotIn('url', $urls)
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);

        DB::table('menu_items')
            ->where('url', '/department/artificial-intelligence-digitalization')
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);

        foreach ($this->items() as $url => $item) {
            $menuItemId = DB::table('menu_items')
                ->where('menu_id', $menuId)
                ->where('url', $url)
                ->value('id');

            $payload = [
                'menu_id' => $menuId,
                'parent_id' => $facultyItem->id,
                'route_name' => 'link',
                'url' => $url,
                'icon' => null,
                'sort_order' => $item['sort_order'],
                'is_active' => true,
                'updated_at' => now(),
            ];

            if ($menuItemId) {
                DB::table('menu_items')->where('id', $menuItemId)->update($payload);
            } else {
                $menuItemId = DB::table('menu_items')->insertGetId($payload + ['created_at' => now()]);
            }

            foreach ($item['labels'] as $locale => $label) {
                DB::table('menu_item_translations')->updateOrInsert(
                    ['menu_item_id' => $menuItemId, 'locale' => $locale],
                    [
                        'label' => $label,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        DB::table('menu_items')
            ->whereIn('url', array_keys($this->items()))
            ->update([
                'is_active' => false,
                'updated_at' => now(),
            ]);
    }

    private function items(): array
    {
        return [
            '/department/technological-processes-production-automation' => [
                'sort_order' => 1,
                'labels' => [
                    'en' => 'Department of Automation of Technological Processes and Production',
                    'uz' => 'Texnologik jarayonlar va ishlab chiqarishni avtomatlashtirish kafedrasi',
                    'ru' => 'Кафедра автоматизации технологических процессов и производства',
                    'ar' => 'قسم أتمتة العمليات التكنولوجية والإنتاج',
                ],
            ],
            '/department/information-and-communication-technologies' => [
                'sort_order' => 2,
                'labels' => [
                    'en' => 'Department of Information and Communication Technologies',
                    'uz' => 'Axborot-kommunikatsiya texnologiyalari kafedrasi',
                    'ru' => 'Кафедра информационно-коммуникационных технологий',
                    'ar' => 'قسم تكنولوجيا المعلومات والاتصالات',
                ],
            ],
            '/department/economics-and-management' => [
                'sort_order' => 3,
                'labels' => [
                    'en' => 'Department of Economics and Management',
                    'uz' => 'Iqtisodiyot va menejment kafedrasi',
                    'ru' => 'Кафедра экономики и менеджмента',
                    'ar' => 'قسم الاقتصاد والإدارة',
                ],
            ],
            '/department/social-sciences-physical-culture' => [
                'sort_order' => 4,
                'labels' => [
                    'en' => 'Department of Social Sciences and Physical Culture',
                    'uz' => 'Ijtimoiy fanlar va jismoniy madaniyat kafedrasi',
                    'ru' => 'Кафедра социальных наук и физической культуры',
                    'ar' => 'قسم العلوم الاجتماعية والثقافة البدنية',
                ],
            ],
            '/department/exact-sciences' => [
                'sort_order' => 5,
                'labels' => [
                    'en' => 'Department of Exact Sciences',
                    'uz' => 'Aniq fanlar kafedrasi',
                    'ru' => 'Кафедра точных наук',
                    'ar' => 'قسم العلوم الدقيقة',
                ],
            ],
            '/department/uzbek-foreign-languages' => [
                'sort_order' => 6,
                'labels' => [
                    'en' => 'Department of Uzbek and Foreign Languages',
                    'uz' => 'O‘zbek va xorijiy tillar kafedrasi',
                    'ru' => 'Кафедра узбекского и иностранных языков',
                    'ar' => 'قسم اللغة الأوزبكية واللغات الأجنبية',
                ],
            ],
        ];
    }
};

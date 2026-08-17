<?php

use App\Models\AboutPage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $pageId = $this->aboutPageId();

        DB::table('about_pages')->where('id', $pageId)->update([
            'hero_contact_url' => '/contact',
            'hero_campus_url' => '/video-bdtu',
            'identity_image' => 'cms/about-page/bstu-about-identity.jpg',
            'rector_profile_slug' => 'rector',
            'is_published' => true,
            'updated_at' => now(),
        ]);

        foreach ($this->facultyCards() as $index => $card) {
            foreach (['id', 'link', 'deanProfile', 'color'] as $field) {
                $this->writePath($pageId, "facultiesList.items.{$index}.{$field}", $this->sameForAllLocales($card[$field] ?? ''), 'text');
            }

            foreach ($this->localizedFacultyFields($card) as $field => $values) {
                $this->writePath($pageId, "facultiesList.items.{$index}.{$field}", $values, 'text');
            }
        }

        $this->writePath($pageId, 'rector.position', [
            'en' => 'Rector of the University',
            'uz' => 'Universitet rektori',
            'ru' => 'Ректор университета',
            'ar' => 'رئيسة الجامعة',
        ], 'text');

        $page = AboutPage::with('contentEntries.translations')->find($pageId);
        $page?->syncTranslationsMirror();

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    private function aboutPageId(): int
    {
        $page = DB::table('about_pages')->where('key', 'main')->first();

        if ($page) {
            return (int) $page->id;
        }

        return (int) DB::table('about_pages')->insertGetId([
            'key' => 'main',
            'hero_contact_url' => '/contact',
            'hero_campus_url' => '/video-bdtu',
            'identity_image' => 'cms/about-page/bstu-about-identity.jpg',
            'rector_profile_slug' => 'rector',
            'is_published' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function writePath(int $pageId, string $path, array $localeValues, string $valueType): void
    {
        $entry = DB::table('about_page_content_entries')
            ->where('about_page_id', $pageId)
            ->where('path', $path)
            ->first();

        if ($entry) {
            DB::table('about_page_content_entries')->where('id', $entry->id)->update([
                'value_type' => $valueType,
                'is_active' => true,
                'updated_at' => now(),
            ]);
            $entryId = (int) $entry->id;
        } else {
            $entryId = (int) DB::table('about_page_content_entries')->insertGetId([
                'about_page_id' => $pageId,
                'path' => $path,
                'value_type' => $valueType,
                'sort_order' => $this->nextSortOrder($pageId),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($localeValues as $locale => $value) {
            DB::table('about_page_content_entry_translations')->updateOrInsert(
                ['about_page_content_entry_id' => $entryId, 'locale' => $locale],
                ['value' => (string) $value, 'created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    private function nextSortOrder(int $pageId): int
    {
        return ((int) DB::table('about_page_content_entries')
            ->where('about_page_id', $pageId)
            ->max('sort_order')) + 1;
    }

    private function sameForAllLocales(string $value): array
    {
        return [
            'en' => $value,
            'uz' => $value,
            'ru' => $value,
            'ar' => $value,
        ];
    }

    private function localizedFacultyFields(array $card): array
    {
        return [
            'name' => $card['name'],
            'dean' => $card['dean'],
            'count' => $card['count'],
            'desc' => $card['desc'],
        ];
    }

    private function facultyCards(): array
    {
        return [
            [
                'id' => 'faculty-of-engineering',
                'link' => '/faculty/faculty-of-engineering',
                'deanProfile' => '/profile/faculty-of-engineering-dean-xojiyev-aziz-kholmurodovich',
                'color' => 'border-blue-500/20 hover:border-blue-500',
                'name' => [
                    'en' => 'Faculty of Engineering',
                    'uz' => 'Muhandislik fakulteti',
                    'ru' => 'Инженерный факультет',
                    'ar' => 'كلية الهندسة',
                ],
                'dean' => [
                    'en' => 'Dr. Khojiyev Aziz Kholmurodovich',
                    'uz' => 'Xojiyev Aziz Kholmurodovich',
                    'ru' => 'Ходжиев Азиз Холмуродович',
                    'ar' => 'د. خوجييف عزيز خولمورودوفيتش',
                ],
                'count' => [
                    'en' => '6 Departments',
                    'uz' => '6 kafedra',
                    'ru' => '6 кафедр',
                    'ar' => '6 أقسام',
                ],
                'desc' => [
                    'en' => 'Engineering education in energy, electrical systems, architecture, civil engineering, light industry, mechanics, and technological equipment.',
                    'uz' => 'Energetika, elektrotexnika, arxitektura, qurilish, yengil sanoat, mexanika va texnologik jihozlar bo‘yicha muhandislik taʼlimi.',
                    'ru' => 'Инженерное образование в энергетике, электротехнике, архитектуре, строительстве, легкой промышленности, механике и технологическом оборудовании.',
                    'ar' => 'تعليم هندسي في الطاقة والأنظمة الكهربائية والعمارة والهندسة المدنية والصناعة الخفيفة والميكانيكا والمعدات التكنولوجية.',
                ],
            ],
            [
                'id' => 'faculty-of-technology',
                'link' => '/faculty/faculty-of-technology',
                'deanProfile' => '/profile/faculty-of-technology-dean-adizov-rashid-tukhtayevich',
                'color' => 'border-purple-500/20 hover:border-purple-500',
                'name' => [
                    'en' => 'Faculty of Technology',
                    'uz' => 'Texnologiya fakulteti',
                    'ru' => 'Технологический факультет',
                    'ar' => 'كلية التكنولوجيا',
                ],
                'dean' => [
                    'en' => 'Dr. Adizov Rashid Tokhtayevich',
                    'uz' => 'Adizov Rashid To‘xtayevich',
                    'ru' => 'Адизов Рашид Тухтаевич',
                    'ar' => 'د. أديزوف رشيد توختاييفيتش',
                ],
                'count' => [
                    'en' => '6 Departments',
                    'uz' => '6 kafedra',
                    'ru' => '6 кафедр',
                    'ar' => '6 أقسام',
                ],
                'desc' => [
                    'en' => 'Technology education for oil and gas refining, food production, chemical technologies, agricultural processing, metrology, and standardization.',
                    'uz' => 'Neft-gazni qayta ishlash, oziq-ovqat ishlab chiqarish, kimyoviy texnologiyalar, qishloq xo‘jaligi mahsulotlarini qayta ishlash, metrologiya va standartlashtirish bo‘yicha taʼlim.',
                    'ru' => 'Образование в области переработки нефти и газа, пищевого производства, химических технологий, переработки сельхозпродукции, метрологии и стандартизации.',
                    'ar' => 'تعليم تقني في تكرير النفط والغاز وإنتاج الغذاء والتقنيات الكيميائية ومعالجة المنتجات الزراعية والمترولوجيا والتقييس.',
                ],
            ],
            [
                'id' => 'faculty-of-service-and-digitalization',
                'link' => '/faculty/faculty-of-service-and-digitalization',
                'deanProfile' => '/profile/faculty-of-service-and-digitalization-dean-khayitov-sherbek-nayimovich',
                'color' => 'border-emerald-500/20 hover:border-emerald-500',
                'name' => [
                    'en' => 'Faculty of Service and Digitalization',
                    'uz' => 'Servis va raqamlashtirish fakulteti',
                    'ru' => 'Факультет сервиса и цифровизации',
                    'ar' => 'كلية الخدمات والرقمنة',
                ],
                'dean' => [
                    'en' => 'Dr. Khayitov Sherbek Nayimovich',
                    'uz' => 'Xayitov Sherbek Nayimovich',
                    'ru' => 'Хайитов Шербек Найимович',
                    'ar' => 'د. خاييتوف شيربيك ناييموفيتش',
                ],
                'count' => [
                    'en' => '6 Departments',
                    'uz' => '6 kafedra',
                    'ru' => '6 кафедр',
                    'ar' => '6 أقسام',
                ],
                'desc' => [
                    'en' => 'Digital economy, information technologies, automation, economics, management, exact sciences, social sciences, and language training.',
                    'uz' => 'Raqamli iqtisodiyot, axborot texnologiyalari, avtomatlashtirish, iqtisodiyot, menejment, aniq fanlar, ijtimoiy fanlar va tillar bo‘yicha taʼlim.',
                    'ru' => 'Цифровая экономика, информационные технологии, автоматизация, экономика, менеджмент, точные науки, социальные науки и языковая подготовка.',
                    'ar' => 'الاقتصاد الرقمي وتقنيات المعلومات والأتمتة والاقتصاد والإدارة والعلوم الدقيقة والعلوم الاجتماعية والتدريب اللغوي.',
                ],
            ],
            [
                'id' => 'faculty-of-natural-resources-management',
                'link' => '/faculty/faculty-of-natural-resources-management',
                'deanProfile' => '/profile/faculty-of-natural-resources-management-dean-qobulova-barno-bakhriddin-qizi',
                'color' => 'border-amber-500/20 hover:border-amber-500',
                'name' => [
                    'en' => 'Faculty of Natural Resources Management',
                    'uz' => 'Tabiiy resurslarni boshqarish fakulteti',
                    'ru' => 'Факультет управления природными ресурсами',
                    'ar' => 'كلية إدارة الموارد الطبيعية',
                ],
                'dean' => [
                    'en' => 'Dr. Barno Bakriddin Kobulova',
                    'uz' => 'Qobulova Barno Bakhriddin qizi',
                    'ru' => 'Кобулова Барно Бахриддин кизи',
                    'ar' => 'د. بارنو بكر الدين كوبولوفا',
                ],
                'count' => [
                    'en' => '6 Departments',
                    'uz' => '6 kafedra',
                    'ru' => '6 кафедр',
                    'ar' => '6 أقسام',
                ],
                'desc' => [
                    'en' => 'Natural resource management, irrigation, hydrotechnical systems, land cadastre, ecology, hydrogeology, agricultural engineering, and vehicle engineering.',
                    'uz' => 'Tabiiy resurslarni boshqarish, irrigatsiya, gidrotexnika tizimlari, yer kadastri, ekologiya, gidrogeologiya, qishloq xo‘jaligi muhandisligi va transport muhandisligi.',
                    'ru' => 'Управление природными ресурсами, ирригация, гидротехнические системы, земельный кадастр, экология, гидрогеология, сельскохозяйственная и транспортная инженерия.',
                    'ar' => 'إدارة الموارد الطبيعية والري والأنظمة الهيدروليكية والسجل العقاري والبيئة والهيدروجيولوجيا والهندسة الزراعية وهندسة المركبات.',
                ],
            ],
        ];
    }
};

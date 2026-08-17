<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('departments') || ! Schema::hasTable('department_translations')) {
            return;
        }

        $departmentId = DB::table('departments')->where('slug', 'irrigation-melioration')->value('id');
        if (! $departmentId) {
            return;
        }

        foreach ($this->researchByLocale() as $locale => $items) {
            $translation = DB::table('department_translations')
                ->where('department_id', $departmentId)
                ->where('locale', $locale)
                ->first();

            if (! $translation) {
                continue;
            }

            $sections = json_decode((string) $translation->content_sections, true);
            if (! is_array($sections)) {
                $sections = [];
            }

            $found = false;
            foreach ($sections as $index => $section) {
                if (($section['key'] ?? null) === 'research') {
                    $sections[$index] = [
                        'key' => 'research',
                        'title' => $this->title($locale),
                        'items' => $items,
                    ];
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                $sections[] = [
                    'key' => 'research',
                    'title' => $this->title($locale),
                    'items' => $items,
                ];
            }

            DB::table('department_translations')
                ->where('id', $translation->id)
                ->update([
                    'content_sections' => json_encode(array_values($sections), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => now(),
                ]);
        }

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function title(string $locale): string
    {
        return match ($locale) {
            'uz' => 'Ilmiy-tadqiqot ishlari',
            'ru' => 'Научно-исследовательские работы',
            'ar' => 'الأعمال البحثية',
            default => 'Research Work',
        };
    }

    private function researchByLocale(): array
    {
        return [
            'en' => [
                'Murodov O.U., Juraev A.K., Khamidov M.Kh. Effectiveness of cost-effective irrigation technologies in cultivation of winter wheat in saline soils. AIP Conference Proceedings, 3256(1).',
                'Murodov O.U., Isaev S.Kh. Mathematical model program for assessing the effect of irrigating repeated crops with drainage water on the reclamation condition of lands. Certificate No. DGU 35001, Ministry of Justice of the Republic of Uzbekistan, registered on 15.03.2024.',
                'Murodov O.U., Isaev S.Kh. Recommendations on assessing the effect of irrigating repeated crops with low-mineralized drainage water on the reclamation condition of saline soils. Bukhara, 2024, 32 p.',
                'Murodov O.U., Isaev S.Kh. Mathematical model program for assessing the effect of irrigating repeated crops with drainage water on yield. Certificate No. DGU 35000, Ministry of Justice of the Republic of Uzbekistan, registered on 15.03.2024.',
                'Murodov O.U. Effects of repeated crops irrigated with low-mineralized drainage water, fertilizer rates, and biopreparations on volumetric mass of soil. Academic Research in Modern Science, Washington, USA, 24 October 2024, pp. 186-189.',
                'Murodov O.U., Isaev S.Kh. Assessment of the efficiency of bioprepare in irrigation with low mineralization ditch water. European Journal of Agricultural and Rural Education, Vol. 5, 2024, pp. 13-14.',
                'Khamidov M.K., Balla D., Hamidov A.M. Collector-drainage water in saline and arid irrigation areas for adaptation to climate change. IOP Conference Series: Earth and Environmental Science, 422(1), 012121, 2020.',
                'Khamidov M., Juraev A., Atamuradov B., Rustamova K., Najmiddinov A., Nurbekov A. Effects of deep softener and chemical compounds on mechanical compositions in heavy, difficult-to-ameliorate soils. IOP Conference Series: Earth and Environmental Science, Vol. 1068, 012017, 2022.',
                'Khamidov M.K. et al. Efficiency of drip irrigation technology of cotton in the saline soils of Bukhara oasis. BIO Web of Conferences, Vol. 103, 00019, 2024.',
                'Khamidov M.Kh., Buriev X.B., Juraev A.K., Sharifov F.K., Isabaev K.T. Efficiency of drip irrigation technology of cotton in saline soils of Bukhara oasis. IOP Conference Series: Earth and Environmental Science, Vol. 1138, 012007, 2023.',
                'Khamidov M.Kh. Influence of phytoremediation plants on soil salts. Innovative Technologies in Water Management Complex, Rovno, Ukraine, 2012, pp. 32-34.',
                'Juraev A.K. et al. Effectiveness of cost-effective irrigation technologies in cultivation of winter wheat in saline soils. AIP Conference Proceedings, Vol. 3256, No. 1, 050039, 2025.',
                'Juraev A.Q., Juraev U.A., Murodov O.U., Atamuradov B.N., Najmiddinov M.M., Ruziyeva M.A. Investigating irrigation system by using drainage water in the cultivation of repeated millet crop. BIO Web of Conferences, Vol. 103, 00014, 2024.',
                'Juraev A.K., Khamidov M.K., Atamuradov B.N., Murodov O.U., Rustamova K.B., Najmiddinov M.M. Effect of deep softeners on irrigation, salt washing and cotton yield on heavy-textured soils with difficult meliorative status. IOP Conference Series: Earth and Environmental Science, Vol. 1138, 012006, 2023.',
            ],
            'ru' => [
                'Муродов О.У., Жураев А.К., Хамидов М.Х. Эффективность экономичных технологий орошения при выращивании озимой пшеницы на засоленных почвах. AIP Conference Proceedings, 3256(1).',
                'Муродов О.У., Исаев С.Х. Программа математической модели для оценки влияния орошения повторных культур дренажными водами на мелиоративное состояние земель. Свидетельство № DGU 35001, Министерство юстиции Республики Узбекистан, зарегистрировано 15.03.2024.',
                'Муродов О.У., Исаев С.Х. Рекомендации по оценке влияния орошения повторных культур слабоминерализованными дренажными водами на мелиоративное состояние засоленных почв. Бухара, 2024, 32 с.',
                'Муродов О.У., Исаев С.Х. Программа математической модели для оценки влияния орошения повторных культур дренажными водами на урожайность. Свидетельство № DGU 35000, Министерство юстиции Республики Узбекистан, зарегистрировано 15.03.2024.',
                'Муродов О.У. Влияние орошения повторных культур слабоминерализованными дренажными водами, норм удобрений и биопрепаратов на объемную массу почвы. Academic Research in Modern Science, Вашингтон, США, 24 октября 2024, с. 186-189.',
                'Муродов О.У., Исаев С.Х. Оценка эффективности биопрепарата при орошении слабоминерализованными коллекторно-дренажными водами. European Journal of Agricultural and Rural Education, т. 5, 2024, с. 13-14.',
                'Хамидов М.К., Балла Д., Хамидов А.М. Использование коллекторно-дренажных вод в засоленных и аридных орошаемых районах для адаптации к изменению климата. IOP Conference Series: Earth and Environmental Science, 422(1), 012121, 2020.',
                'Хамидов М., Жураев А., Атамурадов Б., Рустамова К., Нажмиддинов А., Нурбеков А. Влияние глубокорыхлителей и химических соединений на механический состав тяжелых и трудно мелиорируемых почв. IOP Conference Series: Earth and Environmental Science, т. 1068, 012017, 2022.',
                'Хамидов М.К. и др. Эффективность технологии капельного орошения хлопчатника на засоленных почвах Бухарского оазиса. BIO Web of Conferences, т. 103, 00019, 2024.',
                'Хамидов М.Х., Буриев Х.Б., Жураев А.К., Шарифов Ф.К., Исабаев К.Т. Эффективность технологии капельного орошения хлопчатника на засоленных почвах Бухарского оазиса. IOP Conference Series: Earth and Environmental Science, т. 1138, 012007, 2023.',
                'Хамидов М.Х. Влияние фиторемедиационных растений на содержание солей в почве. Инновационные технологии в водохозяйственном комплексе, Ровно, Украина, 2012, с. 32-34.',
                'Жураев А.К. и др. Эффективность экономичных технологий орошения при выращивании озимой пшеницы на засоленных почвах. AIP Conference Proceedings, т. 3256, № 1, 050039, 2025.',
                'Жураев А.К., Жураев У.А., Муродов О.У., Атамурадов Б.Н., Нажмиддинов М.М., Рузиева М.А. Исследование системы орошения с использованием дренажных вод при выращивании повторной культуры проса. BIO Web of Conferences, т. 103, 00014, 2024.',
                'Жураев А.К., Хамидов М.К., Атамурадов Б.Н., Муродов О.У., Рустамова К.Б., Нажмиддинов М.М. Влияние глубокорыхлителей на орошение, промывку солей и урожайность хлопчатника на тяжелых почвах со сложным мелиоративным состоянием. IOP Conference Series: Earth and Environmental Science, т. 1138, 012006, 2023.',
            ],
            'uz' => [
                'Murodov O.U., Jo‘rayev A.K., Xamidov M.X. Sho‘rlangan tuproqlarda kuzgi bug‘doy yetishtirishda tejamkor sug‘orish texnologiyalarining samaradorligi. AIP Conference Proceedings, 3256(1).',
                'Murodov O.U., Isayev S.X. Takroriy ekinlarni zovur suvlari bilan sug‘orishning yerlarning meliorativ holatiga ta’sirini baholash uchun matematik model dasturi. O‘zbekiston Respublikasi Adliya vazirligi guvohnomasi № DGU 35001, 15.03.2024.',
                'Murodov O.U., Isayev S.X. Sho‘rlangan tuproqlar sharoitida takroriy ekinlarni kuchsiz minerallashgan zovur suvlari bilan sug‘orishning meliorativ holatga ta’sirini baholash bo‘yicha tavsiyalar. Buxoro, 2024, 32 b.',
                'Murodov O.U., Isayev S.X. Takroriy ekinlarni zovur suvlari bilan sug‘orishning hosildorlikka ta’sirini baholash uchun matematik model dasturi. O‘zbekiston Respublikasi Adliya vazirligi guvohnomasi № DGU 35000, 15.03.2024.',
                'Murodov O.U. Kuchsiz minerallashgan zovur suvlari, o‘g‘it me’yorlari va biopreparatlar bilan sug‘orilgan takroriy ekinlarning tuproq hajmiy massasiga ta’siri. Academic Research in Modern Science, Vashington, AQSh, 24-oktabr 2024, 186-189-b.',
                'Murodov O.U., Isayev S.X. Kuchsiz minerallashgan zovur suvlari bilan sug‘orishda biopreparat samaradorligini baholash. European Journal of Agricultural and Rural Education, 5-jild, 2024, 13-14-b.',
                'Xamidov M.K., Balla D., Xamidov A.M. Iqlim o‘zgarishiga moslashish uchun sho‘rlangan va qurg‘oqchil sug‘oriladigan hududlarda kollektor-drenaj suvlaridan foydalanish. IOP Conference Series: Earth and Environmental Science, 422(1), 012121, 2020.',
                'Xamidov M., Jo‘rayev A., Atamurodov B., Rustamova K., Najmiddinov A., Nurbekov A. Og‘ir va meliorativ holati qiyin tuproqlarning mexanik tarkibiga chuqur yumshatgichlar va kimyoviy birikmalarning ta’siri. IOP Conference Series: Earth and Environmental Science, 1068-jild, 012017, 2022.',
                'Xamidov M.K. va boshqalar. Buxoro vohasi sho‘rlangan tuproqlarida paxtani tomchilatib sug‘orish texnologiyasi samaradorligi. BIO Web of Conferences, 103-jild, 00019, 2024.',
                'Xamidov M.X., Bo‘riyev X.B., Jo‘rayev A.K., Sharifov F.K., Isabayev K.T. Buxoro vohasi sho‘rlangan tuproqlarida paxtani tomchilatib sug‘orish texnologiyasi samaradorligi. IOP Conference Series: Earth and Environmental Science, 1138-jild, 012007, 2023.',
                'Xamidov M.X. Fitoremediatsiya o‘simliklarining tuproq sho‘rlanishiga ta’siri. Suv xo‘jaligi majmuasida innovatsion texnologiyalar, Rovno, Ukraina, 2012, 32-34-b.',
                'Jo‘rayev A.K. va boshqalar. Sho‘rlangan tuproqlarda kuzgi bug‘doy yetishtirishda tejamkor sug‘orish texnologiyalarining samaradorligi. AIP Conference Proceedings, 3256-jild, № 1, 050039, 2025.',
                'Jo‘rayev A.Q., Jo‘rayev U.A., Murodov O.U., Atamurodov B.N., Najmiddinov M.M., Ro‘ziyeva M.A. Takroriy tariq yetishtirishda zovur suvlari yordamida sug‘orish tizimini tadqiq qilish. BIO Web of Conferences, 103-jild, 00014, 2024.',
                'Jo‘rayev A.K., Xamidov M.K., Atamurodov B.N., Murodov O.U., Rustamova K.B., Najmiddinov M.M. Og‘ir mexanik tarkibli va meliorativ holati qiyin tuproqlarda chuqur yumshatgichlarning sug‘orish, tuz yuvish va paxta hosildorligiga ta’siri. IOP Conference Series: Earth and Environmental Science, 1138-jild, 012006, 2023.',
            ],
            'ar' => [
                'مورودوف O.U.، جوراييف A.K.، خاميدوف M.Kh. فعالية تقنيات الري الاقتصادية في زراعة القمح الشتوي في التربة المالحة. AIP Conference Proceedings، 3256(1).',
                'مورودوف O.U.، إيساييف S.Kh. برنامج نموذج رياضي لتقييم تأثير ري المحاصيل المتكررة بمياه الصرف على الحالة الاستصلاحية للأراضي. شهادة رقم DGU 35001، وزارة العدل بجمهورية أوزبكستان، مسجلة في 15.03.2024.',
                'مورودوف O.U.، إيساييف S.Kh. توصيات لتقييم تأثير ري المحاصيل المتكررة بمياه صرف منخفضة الملوحة على الحالة الاستصلاحية للتربة المالحة. بخارى، 2024، 32 صفحة.',
                'مورودوف O.U.، إيساييف S.Kh. برنامج نموذج رياضي لتقييم تأثير ري المحاصيل المتكررة بمياه الصرف على الإنتاجية. شهادة رقم DGU 35000، وزارة العدل بجمهورية أوزبكستان، مسجلة في 15.03.2024.',
                'مورودوف O.U. تأثير ري المحاصيل المتكررة بمياه صرف منخفضة الملوحة ومعدلات الأسمدة والمستحضرات الحيوية على الكثافة الحجمية للتربة. Academic Research in Modern Science، واشنطن، الولايات المتحدة، 24 أكتوبر 2024، ص. 186-189.',
                'مورودوف O.U.، إيساييف S.Kh. تقييم كفاءة المستحضر الحيوي عند الري بمياه صرف منخفضة الملوحة. European Journal of Agricultural and Rural Education، المجلد 5، 2024، ص. 13-14.',
                'خاميدوف M.K.، بالا D.، خاميدوف A.M. استخدام مياه المجمعات والصرف في مناطق الري المالحة والجافة للتكيف مع تغير المناخ. IOP Conference Series: Earth and Environmental Science، 422(1)، 012121، 2020.',
                'خاميدوف M.، جوراييف A.، أتامورادوف B.، رستاموفا K.، نجم الدينوف A.، نوربيكوف A. تأثير المفككات العميقة والمركبات الكيميائية على التركيب الميكانيكي للتربة الثقيلة وصعبة الاستصلاح. IOP Conference Series: Earth and Environmental Science، المجلد 1068، 012017، 2022.',
                'خاميدوف M.K. وآخرون. كفاءة تقنية الري بالتنقيط للقطن في التربة المالحة بواحة بخارى. BIO Web of Conferences، المجلد 103، 00019، 2024.',
                'خاميدوف M.Kh.، بورييف X.B.، جوراييف A.K.، شريفوف F.K.، إيساباييف K.T. كفاءة تقنية الري بالتنقيط للقطن في التربة المالحة بواحة بخارى. IOP Conference Series: Earth and Environmental Science، المجلد 1138، 012007، 2023.',
                'خاميدوف M.Kh. تأثير نباتات المعالجة النباتية على أملاح التربة. التقنيات المبتكرة في مجمع إدارة المياه، روفنو، أوكرانيا، 2012، ص. 32-34.',
                'جوراييف A.K. وآخرون. فعالية تقنيات الري الاقتصادية في زراعة القمح الشتوي في التربة المالحة. AIP Conference Proceedings، المجلد 3256، العدد 1، 050039، 2025.',
                'جوراييف A.Q.، جوراييف U.A.، مورودوف O.U.، أتامورادوف B.N.، نجم الدينوف M.M.، روزييفا M.A. دراسة نظام الري باستخدام مياه الصرف في زراعة محصول الدخن المتكرر. BIO Web of Conferences، المجلد 103، 00014، 2024.',
                'جوراييف A.K.، خاميدوف M.K.، أتامورادوف B.N.، مورودوف O.U.، رستاموفا K.B.، نجم الدينوف M.M. تأثير المفككات العميقة على الري وغسل الأملاح وإنتاجية القطن في التربة الثقيلة ذات الحالة الاستصلاحية الصعبة. IOP Conference Series: Earth and Environmental Science، المجلد 1138، 012006، 2023.',
            ],
        ];
    }
};

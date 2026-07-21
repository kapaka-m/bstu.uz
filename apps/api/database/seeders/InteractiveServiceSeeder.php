<?php

namespace Database\Seeders;

use App\Models\InteractiveServiceSetting;
use App\Models\Service;
use App\Models\ServiceTranslation;
use Illuminate\Database\Seeder;

class InteractiveServiceSeeder extends Seeder
{
    public function run(): void
    {
        $setting = InteractiveServiceSetting::updateOrCreate(
            ['key' => 'main'],
            ['home_limit' => 4, 'is_active' => true]
        );

        $settingsTranslations = [
            'en' => [
                'home_tag' => 'Interactive Services',
                'home_title' => 'Access Digital University Services',
                'view_all_label' => 'View All Services',
                'loading_label' => 'Loading services...',
                'no_results_label' => 'No interactive services are available right now.',
            ],
            'uz' => [
                'home_tag' => 'Interaktiv xizmatlar',
                'home_title' => 'Raqamli universitet xizmatlaridan foydalaning',
                'view_all_label' => 'Barcha xizmatlar',
                'loading_label' => 'Xizmatlar yuklanmoqda...',
                'no_results_label' => 'Hozircha interaktiv xizmatlar mavjud emas.',
            ],
            'ru' => [
                'home_tag' => 'Интерактивные сервисы',
                'home_title' => 'Доступ к цифровым университетским сервисам',
                'view_all_label' => 'Все сервисы',
                'loading_label' => 'Загрузка сервисов...',
                'no_results_label' => 'Интерактивные сервисы пока недоступны.',
            ],
            'ar' => [
                'home_tag' => 'الخدمات التفاعلية',
                'home_title' => 'الوصول إلى خدمات الجامعة الرقمية',
                'view_all_label' => 'عرض جميع الخدمات',
                'loading_label' => 'جار تحميل الخدمات...',
                'no_results_label' => 'لا توجد خدمات تفاعلية متاحة حاليًا.',
            ],
        ];

        foreach ($settingsTranslations as $locale => $fields) {
            $setting->translations()->updateOrCreate(['locale' => $locale], $fields);
        }

        $services = [
            [
                'slug' => 'hemis-student-portal',
                'icon' => 'contact',
                'url' => 'https://student.bstu.uz/en/dashboard/login',
                'color' => 'teal',
                'sort_order' => 1,
                'translations' => [
                    'en' => [
                        'title' => 'HEMIS Student Portal',
                        'description' => 'Access your academic schedule, grades, course registration, and attendance records.',
                        'action_label' => 'Login to HEMIS',
                    ],
                    'uz' => [
                        'title' => 'HEMIS talabalar portali',
                        'description' => 'Dars jadvali, baholar, fanlarga ro‘yxatdan o‘tish va davomat ma’lumotlarini ko‘ring.',
                        'action_label' => 'HEMISga kirish',
                    ],
                    'ru' => [
                        'title' => 'Студенческий портал HEMIS',
                        'description' => 'Просматривайте расписание, оценки, регистрацию на курсы и данные посещаемости.',
                        'action_label' => 'Войти в HEMIS',
                    ],
                    'ar' => [
                        'title' => 'بوابة الطالب HEMIS',
                        'description' => 'يمكنك الوصول إلى جدولك الأكاديمي والدرجات وتسجيل المقررات وسجلات الحضور.',
                        'action_label' => 'تسجيل الدخول إلى HEMIS',
                    ],
                ],
            ],
            [
                'slug' => 'office-of-the-registrar',
                'icon' => 'pie-chart',
                'url' => 'https://ro.bstu.uz/student/login',
                'color' => 'red',
                'sort_order' => 2,
                'translations' => [
                    'en' => [
                        'title' => 'Office of the Registrar',
                        'description' => 'Manage your academic records, requests, official transcripts, and enrollment certificates.',
                        'action_label' => 'Access Registrar Office',
                    ],
                    'uz' => [
                        'title' => 'Registrator ofisi',
                        'description' => 'Akademik yozuvlar, so‘rovlar, rasmiy transkriptlar va o‘qishga qabul sertifikatlarini boshqaring.',
                        'action_label' => 'Registrator ofisiga kirish',
                    ],
                    'ru' => [
                        'title' => 'Офис регистратора',
                        'description' => 'Управляйте академическими записями, запросами, официальными транскриптами и справками о зачислении.',
                        'action_label' => 'Открыть офис регистратора',
                    ],
                    'ar' => [
                        'title' => 'مكتب المسجل',
                        'description' => 'إدارة السجلات الأكاديمية والطلبات والكشوف الرسمية وشهادات القيد.',
                        'action_label' => 'الدخول إلى مكتب المسجل',
                    ],
                ],
            ],
            [
                'slug' => 'distance-learning-platform',
                'icon' => 'graduation-cap',
                'url' => 'https://mt.bstu.uz/login/index.php',
                'color' => 'indigo',
                'sort_order' => 3,
                'translations' => [
                    'en' => [
                        'title' => 'Distance Learning Platform',
                        'description' => 'Join online lectures, access electronic learning resources, and participate in discussion forums.',
                        'action_label' => 'Open Moodle Platform',
                    ],
                    'uz' => [
                        'title' => 'Masofaviy ta’lim platformasi',
                        'description' => 'Onlayn ma’ruzalarga qo‘shiling, elektron o‘quv resurslaridan foydalaning va forumlarda qatnashing.',
                        'action_label' => 'Moodle platformasini ochish',
                    ],
                    'ru' => [
                        'title' => 'Платформа дистанционного обучения',
                        'description' => 'Подключайтесь к онлайн-лекциям, используйте электронные ресурсы и участвуйте в форумах.',
                        'action_label' => 'Открыть Moodle',
                    ],
                    'ar' => [
                        'title' => 'منصة التعليم عن بُعد',
                        'description' => 'انضم إلى المحاضرات عبر الإنترنت، واستخدم الموارد التعليمية الإلكترونية، وشارك في المنتديات.',
                        'action_label' => 'فتح منصة Moodle',
                    ],
                ],
            ],
            [
                'slug' => 'contract-invoice-portal',
                'icon' => 'credit-card',
                'url' => 'https://kontrakt.edu.uz/login',
                'color' => 'cyan',
                'sort_order' => 4,
                'translations' => [
                    'en' => [
                        'title' => 'Contract & Invoice Portal',
                        'description' => 'Generate your student tuition contract invoice, check payment status, and make online payments.',
                        'action_label' => 'Get Tuition Invoice',
                    ],
                    'uz' => [
                        'title' => 'Shartnoma va hisob-faktura portali',
                        'description' => 'Talabalik kontrakt hisobini yarating, to‘lov holatini tekshiring va onlayn to‘lov qiling.',
                        'action_label' => 'To‘lov hisobini olish',
                    ],
                    'ru' => [
                        'title' => 'Портал договоров и счетов',
                        'description' => 'Сформируйте счет по контракту, проверьте статус оплаты и выполните онлайн-платеж.',
                        'action_label' => 'Получить счет за обучение',
                    ],
                    'ar' => [
                        'title' => 'بوابة العقود والفواتير',
                        'description' => 'أنشئ فاتورة عقد الرسوم الدراسية، وتحقق من حالة الدفع، وقم بالدفع عبر الإنترنت.',
                        'action_label' => 'الحصول على فاتورة الرسوم',
                    ],
                ],
            ],
            [
                'slug' => 'remote-education-management',
                'icon' => 'list-todo',
                'url' => 'http://mb.bstu.uz/',
                'color' => 'pink',
                'sort_order' => 5,
                'translations' => [
                    'en' => [
                        'title' => 'Remote Education Management',
                        'description' => 'Manage distance learning workflows, online learning assignments, and academic coordination tools.',
                        'action_label' => 'Open Remote Platform',
                    ],
                    'uz' => [
                        'title' => 'Masofaviy ta’lim boshqaruvi',
                        'description' => 'Masofaviy ta’lim jarayonlari, onlayn topshiriqlar va akademik muvofiqlashtirish vositalarini boshqaring.',
                        'action_label' => 'Masofaviy platformani ochish',
                    ],
                    'ru' => [
                        'title' => 'Управление дистанционным обучением',
                        'description' => 'Управляйте процессами дистанционного обучения, онлайн-заданиями и академической координацией.',
                        'action_label' => 'Открыть платформу',
                    ],
                    'ar' => [
                        'title' => 'إدارة التعليم عن بُعد',
                        'description' => 'إدارة مسارات التعليم عن بُعد والمهام الإلكترونية وأدوات التنسيق الأكاديمي.',
                        'action_label' => 'فتح منصة التعليم عن بُعد',
                    ],
                ],
            ],
            [
                'slug' => 'anti-corruption-hotline',
                'icon' => 'alert-triangle',
                'url' => 'https://t.me/BDTU_antikorrupsiyabot',
                'color' => 'red',
                'sort_order' => 6,
                'translations' => [
                    'en' => [
                        'title' => 'Anti-Corruption Hotline',
                        'description' => 'Report corruption risks and ethics concerns through the official confidential university channel.',
                        'action_label' => 'Open Telegram Bot',
                    ],
                    'uz' => [
                        'title' => 'Korrupsiyaga qarshi aloqa kanali',
                        'description' => 'Korrupsiya xavflari va odob-axloq masalalari haqida rasmiy maxfiy kanal orqali xabar bering.',
                        'action_label' => 'Telegram botni ochish',
                    ],
                    'ru' => [
                        'title' => 'Антикоррупционный канал',
                        'description' => 'Сообщайте о коррупционных рисках и вопросах этики через официальный конфиденциальный канал университета.',
                        'action_label' => 'Открыть Telegram-бот',
                    ],
                    'ar' => [
                        'title' => 'قناة مكافحة الفساد',
                        'description' => 'أبلغ عن مخاطر الفساد والمخاوف الأخلاقية عبر القناة الجامعية الرسمية بسرية.',
                        'action_label' => 'فتح بوت Telegram',
                    ],
                ],
            ],
            [
                'slug' => 'student-accommodation-portal',
                'icon' => 'home',
                'url' => 'https://ttj.bstu.uz/',
                'color' => 'teal',
                'sort_order' => 7,
                'translations' => [
                    'en' => [
                        'title' => 'Student Accommodation Portal',
                        'description' => 'Apply for dormitory accommodation, track housing requests, and access residence information.',
                        'action_label' => 'Open Accommodation Portal',
                    ],
                    'uz' => [
                        'title' => 'Talabalar turar joyi portali',
                        'description' => 'Yotoqxona uchun ariza yuboring, joylashuv so‘rovlarini kuzating va turar joy ma’lumotlarini oling.',
                        'action_label' => 'Turar joy portalini ochish',
                    ],
                    'ru' => [
                        'title' => 'Портал студенческого проживания',
                        'description' => 'Подавайте заявки на общежитие, отслеживайте запросы и получайте информацию о проживании.',
                        'action_label' => 'Открыть портал проживания',
                    ],
                    'ar' => [
                        'title' => 'بوابة سكن الطلاب',
                        'description' => 'قدّم طلب السكن الجامعي، وتابع طلبات الإقامة، واطلع على معلومات السكن.',
                        'action_label' => 'فتح بوابة السكن',
                    ],
                ],
            ],
            [
                'slug' => 'academic-timetable',
                'icon' => 'calendar',
                'url' => 'https://timetable.bstu.uz/',
                'color' => 'cyan',
                'sort_order' => 8,
                'translations' => [
                    'en' => [
                        'title' => 'Academic Timetable',
                        'description' => 'View class schedules, academic rooms, weekly timetables, and teaching session updates.',
                        'action_label' => 'View Timetable',
                    ],
                    'uz' => [
                        'title' => 'Akademik dars jadvali',
                        'description' => 'Dars jadvali, auditoriyalar, haftalik reja va mashg‘ulot yangilanishlarini ko‘ring.',
                        'action_label' => 'Jadvalni ko‘rish',
                    ],
                    'ru' => [
                        'title' => 'Академическое расписание',
                        'description' => 'Просматривайте расписание занятий, аудитории, недельные планы и обновления учебных сессий.',
                        'action_label' => 'Открыть расписание',
                    ],
                    'ar' => [
                        'title' => 'الجدول الأكاديمي',
                        'description' => 'اعرض جداول المحاضرات والقاعات والخطط الأسبوعية وتحديثات الحصص الدراسية.',
                        'action_label' => 'عرض الجدول',
                    ],
                ],
            ],
            [
                'slug' => 'online-payment-system',
                'icon' => 'credit-card',
                'url' => 'https://pay.bstu.uz/',
                'color' => 'teal',
                'sort_order' => 9,
                'translations' => [
                    'en' => [
                        'title' => 'Online Payment System',
                        'description' => 'Pay tuition, dormitory fees, library fines, and other university charges securely online.',
                        'action_label' => 'Make Payment',
                    ],
                    'uz' => [
                        'title' => 'Onlayn to‘lov tizimi',
                        'description' => 'Kontrakt, yotoqxona, kutubxona va boshqa universitet to‘lovlarini xavfsiz onlayn amalga oshiring.',
                        'action_label' => 'To‘lov qilish',
                    ],
                    'ru' => [
                        'title' => 'Система онлайн-оплаты',
                        'description' => 'Безопасно оплачивайте обучение, общежитие, библиотечные штрафы и другие университетские услуги.',
                        'action_label' => 'Оплатить онлайн',
                    ],
                    'ar' => [
                        'title' => 'نظام الدفع الإلكتروني',
                        'description' => 'ادفع الرسوم الدراسية والسكن والغرامات والخدمات الجامعية الأخرى بأمان عبر الإنترنت.',
                        'action_label' => 'إجراء الدفع',
                    ],
                ],
            ],
            [
                'slug' => 'hemis-teacher-portal',
                'icon' => 'user-check',
                'url' => 'https://hemis.bstu.uz/',
                'color' => 'indigo',
                'sort_order' => 10,
                'translations' => [
                    'en' => [
                        'title' => 'HEMIS Teacher Portal',
                        'description' => 'Faculty members can manage classes, gradebooks, attendance, and academic reports.',
                        'action_label' => 'Login to HEMIS',
                    ],
                    'uz' => [
                        'title' => 'HEMIS o‘qituvchilar portali',
                        'description' => 'Professor-o‘qituvchilar darslar, baholash jurnali, davomat va akademik hisobotlarni boshqaradi.',
                        'action_label' => 'HEMISga kirish',
                    ],
                    'ru' => [
                        'title' => 'Портал преподавателей HEMIS',
                        'description' => 'Преподаватели могут управлять занятиями, журналами оценок, посещаемостью и отчетами.',
                        'action_label' => 'Войти в HEMIS',
                    ],
                    'ar' => [
                        'title' => 'بوابة أعضاء هيئة التدريس HEMIS',
                        'description' => 'يمكن لأعضاء هيئة التدريس إدارة المقررات وسجلات الدرجات والحضور والتقارير الأكاديمية.',
                        'action_label' => 'تسجيل الدخول إلى HEMIS',
                    ],
                ],
            ],
            [
                'slug' => 'digital-library',
                'icon' => 'book-open',
                'url' => 'https://lib.bstu.uz/',
                'color' => 'orange',
                'sort_order' => 11,
                'translations' => [
                    'en' => [
                        'title' => 'Digital Library',
                        'description' => 'Search electronic books, research materials, academic publications, and library resources.',
                        'action_label' => 'Open Library',
                    ],
                    'uz' => [
                        'title' => 'Raqamli kutubxona',
                        'description' => 'Elektron kitoblar, ilmiy materiallar, akademik nashrlar va kutubxona resurslarini qidiring.',
                        'action_label' => 'Kutubxonani ochish',
                    ],
                    'ru' => [
                        'title' => 'Цифровая библиотека',
                        'description' => 'Ищите электронные книги, научные материалы, академические публикации и ресурсы библиотеки.',
                        'action_label' => 'Открыть библиотеку',
                    ],
                    'ar' => [
                        'title' => 'المكتبة الرقمية',
                        'description' => 'ابحث في الكتب الإلكترونية والمواد البحثية والمنشورات الأكاديمية وموارد المكتبة.',
                        'action_label' => 'فتح المكتبة',
                    ],
                ],
            ],
            [
                'slug' => 'dissertations-platform',
                'icon' => 'file-text',
                'url' => 'https://dissertations.bstu.uz/',
                'color' => 'orange',
                'sort_order' => 12,
                'translations' => [
                    'en' => [
                        'title' => 'Dissertations Platform',
                        'description' => 'Access dissertation announcements, research defense materials, and scientific council information.',
                        'action_label' => 'View Dissertations',
                    ],
                    'uz' => [
                        'title' => 'Dissertatsiyalar platformasi',
                        'description' => 'Dissertatsiya e’lonlari, himoya materiallari va ilmiy kengash ma’lumotlaridan foydalaning.',
                        'action_label' => 'Dissertatsiyalarni ko‘rish',
                    ],
                    'ru' => [
                        'title' => 'Платформа диссертаций',
                        'description' => 'Получайте доступ к объявлениям о диссертациях, материалам защит и информации научного совета.',
                        'action_label' => 'Смотреть диссертации',
                    ],
                    'ar' => [
                        'title' => 'منصة الرسائل العلمية',
                        'description' => 'اطلع على إعلانات الرسائل ومواد المناقشات ومعلومات المجالس العلمية.',
                        'action_label' => 'عرض الرسائل العلمية',
                    ],
                ],
            ],
            [
                'slug' => 'scientific-conferences',
                'icon' => 'users',
                'url' => 'https://conferences.bstu.uz/',
                'color' => 'cyan',
                'sort_order' => 13,
                'translations' => [
                    'en' => [
                        'title' => 'Scientific Conferences',
                        'description' => 'Register for conferences, submit papers, and follow university scientific events.',
                        'action_label' => 'Open Conferences',
                    ],
                    'uz' => [
                        'title' => 'Ilmiy konferensiyalar',
                        'description' => 'Konferensiyalarga ro‘yxatdan o‘ting, maqola yuboring va universitet ilmiy tadbirlarini kuzating.',
                        'action_label' => 'Konferensiyalarni ochish',
                    ],
                    'ru' => [
                        'title' => 'Научные конференции',
                        'description' => 'Регистрируйтесь на конференции, отправляйте статьи и следите за научными событиями университета.',
                        'action_label' => 'Открыть конференции',
                    ],
                    'ar' => [
                        'title' => 'المؤتمرات العلمية',
                        'description' => 'سجّل في المؤتمرات، وقدّم الأبحاث، وتابع الفعاليات العلمية في الجامعة.',
                        'action_label' => 'فتح المؤتمرات',
                    ],
                ],
            ],
            [
                'slug' => 'virtual-campus-tour',
                'icon' => 'map',
                'url' => 'https://bstu.uz/tour/',
                'color' => 'teal',
                'sort_order' => 14,
                'translations' => [
                    'en' => [
                        'title' => 'Virtual Campus Tour',
                        'description' => 'Explore BSTU buildings, laboratories, campus spaces, and student facilities online.',
                        'action_label' => 'Start Virtual Tour',
                    ],
                    'uz' => [
                        'title' => 'Virtual kampus sayohati',
                        'description' => 'BuxDTU binolari, laboratoriyalari, kampus hududi va talabalar infratuzilmasini onlayn ko‘ring.',
                        'action_label' => 'Virtual sayohatni boshlash',
                    ],
                    'ru' => [
                        'title' => 'Виртуальный тур по кампусу',
                        'description' => 'Исследуйте здания, лаборатории, территорию кампуса и студенческую инфраструктуру онлайн.',
                        'action_label' => 'Начать виртуальный тур',
                    ],
                    'ar' => [
                        'title' => 'جولة افتراضية في الحرم الجامعي',
                        'description' => 'استكشف مباني BSTU والمختبرات ومساحات الحرم ومرافق الطلاب عبر الإنترنت.',
                        'action_label' => 'بدء الجولة الافتراضية',
                    ],
                ],
            ],
            [
                'slug' => 'kpi-monitoring-system',
                'icon' => 'trending-up',
                'url' => 'https://kpi.bstu.uz/',
                'color' => 'pink',
                'sort_order' => 15,
                'translations' => [
                    'en' => [
                        'title' => 'KPI Monitoring System',
                        'description' => 'Track performance indicators, departmental progress, and institutional reporting metrics.',
                        'action_label' => 'Open KPI System',
                    ],
                    'uz' => [
                        'title' => 'KPI monitoring tizimi',
                        'description' => 'Samaradorlik ko‘rsatkichlari, bo‘limlar rivoji va institutsional hisobot metrikalarini kuzating.',
                        'action_label' => 'KPI tizimini ochish',
                    ],
                    'ru' => [
                        'title' => 'Система мониторинга KPI',
                        'description' => 'Отслеживайте показатели эффективности, прогресс подразделений и отчетные метрики.',
                        'action_label' => 'Открыть KPI',
                    ],
                    'ar' => [
                        'title' => 'نظام متابعة مؤشرات الأداء',
                        'description' => 'تابع مؤشرات الأداء وتقدم الأقسام ومقاييس التقارير المؤسسية.',
                        'action_label' => 'فتح نظام KPI',
                    ],
                ],
            ],
            [
                'slug' => 'official-telegram-channel',
                'icon' => 'send',
                'url' => 'https://t.me/bdtu_uz_rasmiy',
                'color' => 'indigo',
                'sort_order' => 16,
                'translations' => [
                    'en' => [
                        'title' => 'Official Telegram Channel',
                        'description' => 'Follow official university updates, urgent notices, student news, and campus announcements.',
                        'action_label' => 'Open Telegram',
                    ],
                    'uz' => [
                        'title' => 'Rasmiy Telegram kanali',
                        'description' => 'Universitet yangiliklari, muhim xabarlar, talabalar e’lonlari va kampus ma’lumotlarini kuzating.',
                        'action_label' => 'Telegramni ochish',
                    ],
                    'ru' => [
                        'title' => 'Официальный Telegram-канал',
                        'description' => 'Следите за новостями университета, срочными уведомлениями и объявлениями кампуса.',
                        'action_label' => 'Открыть Telegram',
                    ],
                    'ar' => [
                        'title' => 'قناة Telegram الرسمية',
                        'description' => 'تابع تحديثات الجامعة الرسمية والتنبيهات العاجلة وأخبار الطلاب وإعلانات الحرم الجامعي.',
                        'action_label' => 'فتح Telegram',
                    ],
                ],
            ],
            [
                'slug' => 'university-webmail',
                'icon' => 'mail',
                'url' => 'https://webmail.bstu.uz/',
                'color' => 'cyan',
                'sort_order' => 17,
                'translations' => [
                    'en' => [
                        'title' => 'University Webmail',
                        'description' => 'Access official university email accounts for staff, departments, and academic communication.',
                        'action_label' => 'Open Webmail',
                    ],
                    'uz' => [
                        'title' => 'Universitet veb-pochtasi',
                        'description' => 'Xodimlar, bo‘limlar va akademik aloqa uchun rasmiy universitet email hisoblariga kiring.',
                        'action_label' => 'Veb-pochtani ochish',
                    ],
                    'ru' => [
                        'title' => 'Университетская веб-почта',
                        'description' => 'Откройте официальные почтовые аккаунты университета для сотрудников и подразделений.',
                        'action_label' => 'Открыть Webmail',
                    ],
                    'ar' => [
                        'title' => 'البريد الإلكتروني الجامعي',
                        'description' => 'ادخل إلى حسابات البريد الرسمية للموظفين والأقسام والتواصل الأكاديمي.',
                        'action_label' => 'فتح البريد الجامعي',
                    ],
                ],
            ],
        ];

        foreach ($services as $item) {
            $service = Service::updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'icon' => $item['icon'],
                    'url' => $item['url'],
                    'color' => $item['color'],
                    'home_visible' => true,
                    'opens_new_tab' => true,
                    'sort_order' => $item['sort_order'],
                    'is_active' => true,
                ]
            );

            foreach ($item['translations'] as $locale => $fields) {
                ServiceTranslation::updateOrCreate(
                    ['service_id' => $service->id, 'locale' => $locale],
                    $fields
                );
            }
        }
    }
}

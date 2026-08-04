<?php

namespace Database\Seeders;

use App\Models\AuthEmailTemplate;
use App\Models\AuthPage;
use App\Models\Locale;
use Illuminate\Database\Seeder;

class AuthCmsSeeder extends Seeder
{
    public function run(): void
    {
        $locales = Locale::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('code')
            ->values()
            ->all();

        if ($locales === []) {
            $locales = ['en', 'uz', 'ru', 'ar'];
        }

        foreach ($this->pages() as $pageKey => $pageData) {
            $page = AuthPage::updateOrCreate(
                ['page_key' => $pageKey],
                [
                    'is_active' => true,
                    'settings' => $pageData['settings'] ?? [],
                ],
            );

            foreach ($locales as $locale) {
                $fields = $pageData['translations'][$locale]
                    ?? $pageData['translations']['en']
                    ?? [];

                $page->translations()->updateOrCreate(
                    ['locale' => $locale],
                    $fields + ['locale' => $locale],
                );
            }
        }

        foreach ($this->emailTemplates() as $templateKey => $templateData) {
            $template = AuthEmailTemplate::updateOrCreate(
                ['template_key' => $templateKey],
                [
                    'is_active' => true,
                    'settings' => $templateData['settings'] ?? [],
                ],
            );

            foreach ($locales as $locale) {
                $fields = $templateData['translations'][$locale]
                    ?? $templateData['translations']['en']
                    ?? [];

                $template->translations()->updateOrCreate(
                    ['locale' => $locale],
                    $fields + ['locale' => $locale],
                );
            }
        }
    }

    protected function pages(): array
    {
        return [
            'login' => [
                'settings' => [
                    'secondary_action_url' => '/apply',
                    'intended_role' => 'student',
                ],
                'translations' => [
                    'en' => [
                        'title' => 'Welcome Back',
                        'subtitle' => 'Sign in to access your student portal and application dashboard.',
                        'email_label' => 'Email Address',
                        'email_placeholder' => 'email@example.com',
                        'password_label' => 'Password',
                        'password_placeholder' => 'Password',
                        'submit_label' => 'Log In',
                        'loading_label' => 'Logging in...',
                        'forgot_password_label' => 'Forgot Password?',
                        'secondary_text' => "Don't have an account?",
                        'secondary_action_label' => 'Apply',
                        'secondary_action_url' => '/apply',
                        'back_label' => 'Back to Log In',
                        'show_password_label' => 'Show password',
                        'hide_password_label' => 'Hide password',
                        'validation_required_message' => 'Please enter your email address and password.',
                        'error_message' => 'Login failed.',
                        'logo_alt' => 'BSTU logo',
                    ],
                    'uz' => [
                        'title' => 'Xush Kelibsiz',
                        'subtitle' => 'Talaba portali va ariza kabinetiga kirish uchun tizimga kiring.',
                        'email_label' => 'Email manzil',
                        'email_placeholder' => 'email@example.com',
                        'password_label' => 'Parol',
                        'password_placeholder' => 'Parol',
                        'submit_label' => 'Kirish',
                        'loading_label' => 'Kirilmoqda...',
                        'forgot_password_label' => 'Parolni unutdingizmi?',
                        'secondary_text' => 'Hisobingiz yo‘qmi?',
                        'secondary_action_label' => 'Ariza topshirish',
                        'secondary_action_url' => '/apply',
                        'back_label' => 'Kirishga qaytish',
                        'show_password_label' => 'Parolni ko‘rsatish',
                        'hide_password_label' => 'Parolni yashirish',
                        'validation_required_message' => 'Email manzil va parolni kiriting.',
                        'error_message' => 'Kirish amalga oshmadi.',
                        'logo_alt' => 'BSTU logotipi',
                    ],
                    'ru' => [
                        'title' => 'С Возвращением',
                        'subtitle' => 'Войдите, чтобы открыть студенческий портал и кабинет заявки.',
                        'email_label' => 'Email адрес',
                        'email_placeholder' => 'email@example.com',
                        'password_label' => 'Пароль',
                        'password_placeholder' => 'Пароль',
                        'submit_label' => 'Войти',
                        'loading_label' => 'Выполняется вход...',
                        'forgot_password_label' => 'Забыли пароль?',
                        'secondary_text' => 'Нет аккаунта?',
                        'secondary_action_label' => 'Подать заявку',
                        'secondary_action_url' => '/apply',
                        'back_label' => 'Вернуться ко входу',
                        'show_password_label' => 'Показать пароль',
                        'hide_password_label' => 'Скрыть пароль',
                        'validation_required_message' => 'Введите email адрес и пароль.',
                        'error_message' => 'Не удалось войти.',
                        'logo_alt' => 'Логотип BSTU',
                    ],
                    'ar' => [
                        'title' => 'مرحبا بعودتك',
                        'subtitle' => 'سجل الدخول للوصول إلى بوابة الطالب ولوحة متابعة الطلب.',
                        'email_label' => 'البريد الإلكتروني',
                        'email_placeholder' => 'email@example.com',
                        'password_label' => 'كلمة المرور',
                        'password_placeholder' => 'كلمة المرور',
                        'submit_label' => 'تسجيل الدخول',
                        'loading_label' => 'جار تسجيل الدخول...',
                        'forgot_password_label' => 'هل نسيت كلمة المرور؟',
                        'secondary_text' => 'ليس لديك حساب؟',
                        'secondary_action_label' => 'قدم الآن',
                        'secondary_action_url' => '/apply',
                        'back_label' => 'العودة إلى تسجيل الدخول',
                        'show_password_label' => 'إظهار كلمة المرور',
                        'hide_password_label' => 'إخفاء كلمة المرور',
                        'validation_required_message' => 'يرجى إدخال البريد الإلكتروني وكلمة المرور.',
                        'error_message' => 'فشل تسجيل الدخول.',
                        'logo_alt' => 'شعار BSTU',
                    ],
                ],
            ],
            'forgot_password' => [
                'settings' => [
                    'secondary_action_url' => '/login',
                ],
                'translations' => [
                    'en' => [
                        'title' => 'Reset Password',
                        'subtitle' => 'Enter your account email and we will send password reset instructions.',
                        'email_label' => 'Email Address',
                        'email_placeholder' => 'email@example.com',
                        'submit_label' => 'Send Reset Link',
                        'loading_label' => 'Sending instructions...',
                        'success_title' => 'Check your email',
                        'success_message' => 'We sent password reset instructions to {email}.',
                        'back_label' => 'Back to Log In',
                        'error_message' => 'We could not send reset instructions.',
                        'logo_alt' => 'BSTU logo',
                    ],
                    'uz' => [
                        'title' => 'Parolni tiklash',
                        'subtitle' => 'Hisobingiz email manzilini kiriting, biz parolni tiklash yo‘riqnomasini yuboramiz.',
                        'email_label' => 'Email manzil',
                        'email_placeholder' => 'email@example.com',
                        'submit_label' => 'Tiklash havolasini yuborish',
                        'loading_label' => 'Yo‘riqnoma yuborilmoqda...',
                        'success_title' => 'Emailingizni tekshiring',
                        'success_message' => 'Parolni tiklash yo‘riqnomasi {email} manziliga yuborildi.',
                        'back_label' => 'Kirishga qaytish',
                        'error_message' => 'Tiklash yo‘riqnomasini yuborib bo‘lmadi.',
                        'logo_alt' => 'BSTU logotipi',
                    ],
                    'ru' => [
                        'title' => 'Сброс Пароля',
                        'subtitle' => 'Введите email аккаунта, и мы отправим инструкции по сбросу пароля.',
                        'email_label' => 'Email адрес',
                        'email_placeholder' => 'email@example.com',
                        'submit_label' => 'Отправить ссылку',
                        'loading_label' => 'Отправляем инструкции...',
                        'success_title' => 'Проверьте почту',
                        'success_message' => 'Инструкции по сбросу пароля отправлены на {email}.',
                        'back_label' => 'Вернуться ко входу',
                        'error_message' => 'Не удалось отправить инструкции по сбросу.',
                        'logo_alt' => 'Логотип BSTU',
                    ],
                    'ar' => [
                        'title' => 'إعادة تعيين كلمة المرور',
                        'subtitle' => 'أدخل بريد حسابك وسنرسل تعليمات إعادة تعيين كلمة المرور.',
                        'email_label' => 'البريد الإلكتروني',
                        'email_placeholder' => 'email@example.com',
                        'submit_label' => 'إرسال رابط إعادة التعيين',
                        'loading_label' => 'جار إرسال التعليمات...',
                        'success_title' => 'تحقق من بريدك',
                        'success_message' => 'تم إرسال تعليمات إعادة تعيين كلمة المرور إلى {email}.',
                        'back_label' => 'العودة إلى تسجيل الدخول',
                        'error_message' => 'تعذر إرسال تعليمات إعادة التعيين.',
                        'logo_alt' => 'شعار BSTU',
                    ],
                ],
            ],
            'reset_password' => [
                'settings' => [
                    'secondary_action_url' => '/login',
                ],
                'translations' => [
                    'en' => [
                        'title' => 'Reset Password',
                        'email_label' => 'Email Address',
                        'email_placeholder' => 'email@example.com',
                        'password_label' => 'Password',
                        'password_placeholder' => 'Password',
                        'confirm_password_label' => 'Confirm Password',
                        'confirm_password_placeholder' => 'Confirm Password',
                        'submit_label' => 'Reset Password',
                        'loading_label' => 'Resetting password...',
                        'success_title' => 'Password reset',
                        'success_message' => 'Password reset successfully. You can now log in with your new password.',
                        'back_label' => 'Back to Log In',
                        'show_password_label' => 'Show password',
                        'hide_password_label' => 'Hide password',
                        'validation_required_message' => 'Please complete all required fields.',
                        'validation_mismatch_message' => 'Passwords do not match.',
                        'error_message' => 'Invalid or expired password reset token.',
                        'logo_alt' => 'BSTU logo',
                    ],
                    'uz' => [
                        'title' => 'Parolni tiklash',
                        'email_label' => 'Email manzil',
                        'email_placeholder' => 'email@example.com',
                        'password_label' => 'Parol',
                        'password_placeholder' => 'Parol',
                        'confirm_password_label' => 'Parolni tasdiqlang',
                        'confirm_password_placeholder' => 'Parolni tasdiqlang',
                        'submit_label' => 'Parolni tiklash',
                        'loading_label' => 'Parol tiklanmoqda...',
                        'success_title' => 'Parol tiklandi',
                        'success_message' => 'Parol muvaffaqiyatli tiklandi. Endi yangi parol bilan tizimga kirishingiz mumkin.',
                        'back_label' => 'Kirishga qaytish',
                        'show_password_label' => 'Parolni ko‘rsatish',
                        'hide_password_label' => 'Parolni yashirish',
                        'validation_required_message' => 'Barcha majburiy maydonlarni to‘ldiring.',
                        'validation_mismatch_message' => 'Parollar mos kelmadi.',
                        'error_message' => 'Parolni tiklash tokeni noto‘g‘ri yoki muddati tugagan.',
                        'logo_alt' => 'BSTU logotipi',
                    ],
                    'ru' => [
                        'title' => 'Сброс Пароля',
                        'email_label' => 'Email адрес',
                        'email_placeholder' => 'email@example.com',
                        'password_label' => 'Пароль',
                        'password_placeholder' => 'Пароль',
                        'confirm_password_label' => 'Подтвердите пароль',
                        'confirm_password_placeholder' => 'Подтвердите пароль',
                        'submit_label' => 'Сбросить пароль',
                        'loading_label' => 'Сбрасываем пароль...',
                        'success_title' => 'Пароль сброшен',
                        'success_message' => 'Пароль успешно сброшен. Теперь вы можете войти с новым паролем.',
                        'back_label' => 'Вернуться ко входу',
                        'show_password_label' => 'Показать пароль',
                        'hide_password_label' => 'Скрыть пароль',
                        'validation_required_message' => 'Заполните все обязательные поля.',
                        'validation_mismatch_message' => 'Пароли не совпадают.',
                        'error_message' => 'Токен сброса пароля недействителен или истек.',
                        'logo_alt' => 'Логотип BSTU',
                    ],
                    'ar' => [
                        'title' => 'إعادة تعيين كلمة المرور',
                        'email_label' => 'البريد الإلكتروني',
                        'email_placeholder' => 'email@example.com',
                        'password_label' => 'كلمة المرور',
                        'password_placeholder' => 'كلمة المرور',
                        'confirm_password_label' => 'تأكيد كلمة المرور',
                        'confirm_password_placeholder' => 'تأكيد كلمة المرور',
                        'submit_label' => 'إعادة تعيين كلمة المرور',
                        'loading_label' => 'جار إعادة التعيين...',
                        'success_title' => 'تمت إعادة تعيين كلمة المرور',
                        'success_message' => 'تمت إعادة تعيين كلمة المرور بنجاح. يمكنك الآن تسجيل الدخول بكلمة المرور الجديدة.',
                        'back_label' => 'العودة إلى تسجيل الدخول',
                        'show_password_label' => 'إظهار كلمة المرور',
                        'hide_password_label' => 'إخفاء كلمة المرور',
                        'validation_required_message' => 'يرجى إكمال جميع الحقول المطلوبة.',
                        'validation_mismatch_message' => 'كلمتا المرور غير متطابقتين.',
                        'error_message' => 'رمز إعادة تعيين كلمة المرور غير صالح أو منتهي الصلاحية.',
                        'logo_alt' => 'شعار BSTU',
                    ],
                ],
            ],
        ];
    }

    protected function emailTemplates(): array
    {
        return [
            'password_reset' => [
                'settings' => [
                    'brand_url' => config('app.url'),
                    'button_color' => '#18181b',
                    'accent_color' => '#fafafa',
                    'frontend_reset_path' => '/reset-password',
                    'expire_minutes' => 60,
                ],
                'translations' => [
                    'en' => [
                        'subject' => 'Reset your password',
                        'brand_name' => 'Platform BSTU',
                        'greeting' => 'Hello!',
                        'intro' => 'You are receiving this email because we received a password reset request for your account.',
                        'action_label' => 'Reset Password',
                        'expiry_notice' => 'This password reset link will expire in {minutes} minutes.',
                        'no_action_notice' => 'If you did not request a password reset, no further action is required.',
                        'salutation' => 'Regards,',
                        'signature' => 'Platform BSTU',
                        'subcopy' => 'If you are having trouble clicking the "{action_label}" button, copy and paste the URL below into your web browser:',
                        'footer' => '© {year} Platform BSTU. All rights reserved.',
                    ],
                    'uz' => [
                        'subject' => 'Parolingizni tiklash',
                        'brand_name' => 'Platform BSTU',
                        'greeting' => 'Assalomu alaykum!',
                        'intro' => 'Hisobingiz uchun parolni tiklash so‘rovi olingani sababli ushbu xabar yuborildi.',
                        'action_label' => 'Parolni tiklash',
                        'expiry_notice' => 'Ushbu parolni tiklash havolasi {minutes} daqiqadan so‘ng amal qilishni to‘xtatadi.',
                        'no_action_notice' => 'Agar parolni tiklashni so‘ramagan bo‘lsangiz, hech qanday amal talab qilinmaydi.',
                        'salutation' => 'Hurmat bilan,',
                        'signature' => 'Platform BSTU',
                        'subcopy' => 'Agar "{action_label}" tugmasini bosishda muammo bo‘lsa, quyidagi URL manzilni brauzeringizga ko‘chirib joylashtiring:',
                        'footer' => '© {year} Platform BSTU. Barcha huquqlar himoyalangan.',
                    ],
                    'ru' => [
                        'subject' => 'Сброс пароля',
                        'brand_name' => 'Platform BSTU',
                        'greeting' => 'Здравствуйте!',
                        'intro' => 'Вы получили это письмо, потому что мы получили запрос на сброс пароля для вашей учетной записи.',
                        'action_label' => 'Сбросить пароль',
                        'expiry_notice' => 'Срок действия этой ссылки для сброса пароля истечет через {minutes} минут.',
                        'no_action_notice' => 'Если вы не запрашивали сброс пароля, дополнительных действий не требуется.',
                        'salutation' => 'С уважением,',
                        'signature' => 'Platform BSTU',
                        'subcopy' => 'Если у вас не получается нажать кнопку "{action_label}", скопируйте и вставьте ссылку ниже в браузер:',
                        'footer' => '© {year} Platform BSTU. Все права защищены.',
                    ],
                    'ar' => [
                        'subject' => 'إعادة تعيين كلمة المرور',
                        'brand_name' => 'منصة BSTU',
                        'greeting' => 'مرحبا!',
                        'intro' => 'تصلك هذه الرسالة لأننا تلقينا طلبا لإعادة تعيين كلمة المرور الخاصة بحسابك.',
                        'action_label' => 'إعادة تعيين كلمة المرور',
                        'expiry_notice' => 'ستنتهي صلاحية رابط إعادة تعيين كلمة المرور خلال {minutes} دقيقة.',
                        'no_action_notice' => 'إذا لم تطلب إعادة تعيين كلمة المرور، فلا يلزم اتخاذ أي إجراء آخر.',
                        'salutation' => 'مع التحية،',
                        'signature' => 'منصة BSTU',
                        'subcopy' => 'إذا واجهت مشكلة في الضغط على زر "{action_label}"، انسخ الرابط أدناه والصقه في متصفحك:',
                        'footer' => '© {year} منصة BSTU. جميع الحقوق محفوظة.',
                    ],
                ],
            ],
            'newsletter_welcome' => [
                'settings' => [
                    'brand_url' => config('app.url'),
                    'button_color' => '#0d6efd',
                    'accent_color' => '#eef4ff',
                ],
                'translations' => $this->localizedEmailTemplate([
                    'en' => [
                        'subject' => 'Welcome to the BSTU newsletter',
                        'brand_name' => 'BSTU International',
                        'greeting' => 'Thank you for subscribing',
                        'intro' => 'You are now subscribed to the BSTU International newsletter. We will send you selected university updates, academic news, event announcements, and international student opportunities.',
                        'action_label' => 'Visit BSTU International',
                        'expiry_notice' => 'Your subscription email: {email}',
                        'no_action_notice' => 'You will receive only relevant updates from our official platform.',
                        'salutation' => 'Warm regards,',
                        'signature' => 'BSTU International Team',
                        'subcopy' => 'You can visit the website using the link below.',
                        'footer' => '© {year} BSTU International. All rights reserved.',
                    ],
                    'uz' => [
                        'subject' => 'BSTU yangiliklar byulleteniga xush kelibsiz',
                        'brand_name' => 'BSTU International',
                        'greeting' => 'Obuna bo‘lganingiz uchun rahmat',
                        'intro' => 'Siz BSTU International yangiliklar byulleteniga obuna bo‘ldingiz. Endi universitet yangiliklari, akademik xabarlar, tadbir eʼlonlari va xalqaro talabalar uchun imkoniyatlar haqida xabarlar olasiz.',
                        'action_label' => 'BSTU International saytiga o‘tish',
                        'expiry_notice' => 'Obuna email manzilingiz: {email}',
                        'no_action_notice' => 'Biz faqat rasmiy platformamizdagi muhim va dolzarb yangiliklarni yuboramiz.',
                        'salutation' => 'Hurmat bilan,',
                        'signature' => 'BSTU International jamoasi',
                        'subcopy' => 'Saytga quyidagi havola orqali o‘tishingiz mumkin.',
                        'footer' => '© {year} BSTU International. Barcha huquqlar himoyalangan.',
                    ],
                    'ru' => [
                        'subject' => 'Добро пожаловать в рассылку BSTU',
                        'brand_name' => 'BSTU International',
                        'greeting' => 'Спасибо за подписку',
                        'intro' => 'Вы подписались на рассылку BSTU International. Мы будем отправлять вам отобранные новости университета, академические объявления, события и возможности для иностранных студентов.',
                        'action_label' => 'Перейти на BSTU International',
                        'expiry_notice' => 'Email подписки: {email}',
                        'no_action_notice' => 'Вы будете получать только актуальные сообщения с нашей официальной платформы.',
                        'salutation' => 'С уважением,',
                        'signature' => 'Команда BSTU International',
                        'subcopy' => 'Вы можете перейти на сайт по ссылке ниже.',
                        'footer' => '© {year} BSTU International. Все права защищены.',
                    ],
                    'ar' => [
                        'subject' => 'مرحبا بك في النشرة الإخبارية لـ BSTU',
                        'brand_name' => 'BSTU International',
                        'greeting' => 'شكرا لاشتراكك',
                        'intro' => 'تم اشتراكك الآن في النشرة الإخبارية لموقع BSTU International. سنرسل لك أحدث أخبار الجامعة، وإعلانات الفعاليات، والمستجدات الأكاديمية، وفرص الطلاب الدوليين.',
                        'action_label' => 'زيارة موقع BSTU International',
                        'expiry_notice' => 'بريد الاشتراك: {email}',
                        'no_action_notice' => 'ستصلك فقط التحديثات المهمة من منصتنا الرسمية.',
                        'salutation' => 'مع خالص التحية،',
                        'signature' => 'فريق BSTU International',
                        'subcopy' => 'يمكنك زيارة الموقع من خلال الرابط أدناه.',
                        'footer' => '© {year} BSTU International. جميع الحقوق محفوظة.',
                    ],
                ]),
            ],
            'inquiry_received' => [
                'settings' => [
                    'brand_url' => config('app.url'),
                    'button_color' => '#0d6efd',
                    'accent_color' => '#f0fdf4',
                ],
                'translations' => $this->localizedEmailTemplate([
                    'en' => [
                        'subject' => 'We received your inquiry',
                        'brand_name' => 'BSTU International',
                        'greeting' => 'Your inquiry has been received',
                        'intro' => 'Hello {name}, thank you for contacting BSTU International. Your message about "{subject}" has been received and will be reviewed by our team.',
                        'action_label' => 'Visit Contact Page',
                        'expiry_notice' => 'Please follow your email inbox for our reply.',
                        'no_action_notice' => 'We will contact you using this email address as soon as your inquiry is reviewed.',
                        'salutation' => 'Kind regards,',
                        'signature' => 'BSTU International Support',
                        'subcopy' => 'You can return to the contact page using the link below.',
                        'footer' => '© {year} BSTU International. All rights reserved.',
                    ],
                    'uz' => [
                        'subject' => 'Murojaatingiz qabul qilindi',
                        'brand_name' => 'BSTU International',
                        'greeting' => 'Murojaatingiz qabul qilindi',
                        'intro' => 'Assalomu alaykum {name}, BSTU International bilan bog‘langaningiz uchun rahmat. "{subject}" mavzusidagi xabaringiz qabul qilindi va jamoamiz tomonidan ko‘rib chiqiladi.',
                        'action_label' => 'Aloqa sahifasiga o‘tish',
                        'expiry_notice' => 'Javobimizni olish uchun email pochtangizni kuzatib boring.',
                        'no_action_notice' => 'Murojaatingiz ko‘rib chiqilgach, siz bilan shu email orqali bog‘lanamiz.',
                        'salutation' => 'Hurmat bilan,',
                        'signature' => 'BSTU International qo‘llab-quvvatlash xizmati',
                        'subcopy' => 'Aloqa sahifasiga quyidagi havola orqali qaytishingiz mumkin.',
                        'footer' => '© {year} BSTU International. Barcha huquqlar himoyalangan.',
                    ],
                    'ru' => [
                        'subject' => 'Ваш запрос получен',
                        'brand_name' => 'BSTU International',
                        'greeting' => 'Ваш запрос принят',
                        'intro' => 'Здравствуйте, {name}. Спасибо за обращение в BSTU International. Ваше сообщение по теме «{subject}» получено и будет рассмотрено нашей командой.',
                        'action_label' => 'Открыть страницу контактов',
                        'expiry_notice' => 'Пожалуйста, следите за почтой, чтобы получить наш ответ.',
                        'no_action_notice' => 'После рассмотрения запроса мы свяжемся с вами по этому email адресу.',
                        'salutation' => 'С уважением,',
                        'signature' => 'Служба поддержки BSTU International',
                        'subcopy' => 'Вы можете вернуться на страницу контактов по ссылке ниже.',
                        'footer' => '© {year} BSTU International. Все права защищены.',
                    ],
                    'ar' => [
                        'subject' => 'تم استلام استفسارك',
                        'brand_name' => 'BSTU International',
                        'greeting' => 'تم استلام استفسارك بنجاح',
                        'intro' => 'مرحبا {name}، شكرا لتواصلك مع BSTU International. تم استلام رسالتك بخصوص "{subject}" وسيقوم فريقنا بمراجعتها.',
                        'action_label' => 'زيارة صفحة التواصل',
                        'expiry_notice' => 'يرجى متابعة بريدك الإلكتروني لاستلام الرد.',
                        'no_action_notice' => 'سنتواصل معك عبر هذا البريد بعد مراجعة استفسارك.',
                        'salutation' => 'مع خالص التحية،',
                        'signature' => 'فريق دعم BSTU International',
                        'subcopy' => 'يمكنك الرجوع إلى صفحة التواصل من خلال الرابط أدناه.',
                        'footer' => '© {year} BSTU International. جميع الحقوق محفوظة.',
                    ],
                ]),
            ],
            'blog_comment_reply' => [
                'settings' => [
                    'brand_url' => config('app.url'),
                    'button_color' => '#0d6efd',
                    'accent_color' => '#fff7ed',
                ],
                'translations' => $this->localizedEmailTemplate([
                    'en' => [
                        'subject' => 'New reply to your blog comment',
                        'brand_name' => 'BSTU International',
                        'greeting' => 'Someone replied to your comment',
                        'intro' => 'Hello {name}, {reply_author} replied to your comment on "{blog_title}".',
                        'action_label' => 'Open Discussion',
                        'expiry_notice' => 'Reply preview: {reply_excerpt}',
                        'no_action_notice' => 'You can open the blog post to continue the discussion.',
                        'salutation' => 'Best regards,',
                        'signature' => 'BSTU International Team',
                        'subcopy' => 'Use the link below to view the comment thread.',
                        'footer' => '© {year} BSTU International. All rights reserved.',
                    ],
                    'uz' => [
                        'subject' => 'Blog izohingizga yangi javob bor',
                        'brand_name' => 'BSTU International',
                        'greeting' => 'Izohingizga javob yozildi',
                        'intro' => 'Assalomu alaykum {name}, {reply_author} sizning "{blog_title}" maqolasidagi izohingizga javob berdi.',
                        'action_label' => 'Muhokamani ochish',
                        'expiry_notice' => 'Javobdan parcha: {reply_excerpt}',
                        'no_action_notice' => 'Muhokamani davom ettirish uchun blog maqolasini ochishingiz mumkin.',
                        'salutation' => 'Hurmat bilan,',
                        'signature' => 'BSTU International jamoasi',
                        'subcopy' => 'Izohlar mavzusini ko‘rish uchun quyidagi havoladan foydalaning.',
                        'footer' => '© {year} BSTU International. Barcha huquqlar himoyalangan.',
                    ],
                    'ru' => [
                        'subject' => 'Новый ответ на ваш комментарий',
                        'brand_name' => 'BSTU International',
                        'greeting' => 'На ваш комментарий ответили',
                        'intro' => 'Здравствуйте, {name}. {reply_author} ответил(а) на ваш комментарий к публикации «{blog_title}».',
                        'action_label' => 'Открыть обсуждение',
                        'expiry_notice' => 'Фрагмент ответа: {reply_excerpt}',
                        'no_action_notice' => 'Вы можете открыть публикацию, чтобы продолжить обсуждение.',
                        'salutation' => 'С уважением,',
                        'signature' => 'Команда BSTU International',
                        'subcopy' => 'Используйте ссылку ниже, чтобы открыть ветку комментариев.',
                        'footer' => '© {year} BSTU International. Все права защищены.',
                    ],
                    'ar' => [
                        'subject' => 'رد جديد على تعليقك في المدونة',
                        'brand_name' => 'BSTU International',
                        'greeting' => 'تم الرد على تعليقك',
                        'intro' => 'مرحبا {name}، قام {reply_author} بالرد على تعليقك في مقال "{blog_title}".',
                        'action_label' => 'فتح المناقشة',
                        'expiry_notice' => 'معاينة الرد: {reply_excerpt}',
                        'no_action_notice' => 'يمكنك فتح المقال لمتابعة المناقشة.',
                        'salutation' => 'مع خالص التحية،',
                        'signature' => 'فريق BSTU International',
                        'subcopy' => 'استخدم الرابط أدناه لعرض سلسلة التعليقات.',
                        'footer' => '© {year} BSTU International. جميع الحقوق محفوظة.',
                    ],
                ]),
            ],
            'newsletter_campaign' => [
                'settings' => [
                    'brand_url' => config('app.url'),
                    'button_color' => '#0d6efd',
                    'accent_color' => '#eef4ff',
                ],
                'translations' => $this->localizedEmailTemplate([
                    'en' => [
                        'subject' => '{campaign_subject}',
                        'brand_name' => 'BSTU International',
                        'greeting' => '{campaign_title}',
                        'intro' => '{campaign_message}',
                        'action_label' => '{campaign_cta_label}',
                        'expiry_notice' => '',
                        'no_action_notice' => 'Thank you for staying connected with BSTU International.',
                        'salutation' => 'Regards,',
                        'signature' => 'BSTU International Team',
                        'subcopy' => 'This message was sent to active newsletter subscribers.',
                        'footer' => '© {year} BSTU International. All rights reserved.',
                    ],
                    'uz' => [
                        'subject' => '{campaign_subject}',
                        'brand_name' => 'BSTU International',
                        'greeting' => '{campaign_title}',
                        'intro' => '{campaign_message}',
                        'action_label' => '{campaign_cta_label}',
                        'expiry_notice' => '',
                        'no_action_notice' => 'BSTU International bilan aloqada bo‘lganingiz uchun rahmat.',
                        'salutation' => 'Hurmat bilan,',
                        'signature' => 'BSTU International jamoasi',
                        'subcopy' => 'Ushbu xabar faol yangiliklar obunachilariga yuborildi.',
                        'footer' => '© {year} BSTU International. Barcha huquqlar himoyalangan.',
                    ],
                    'ru' => [
                        'subject' => '{campaign_subject}',
                        'brand_name' => 'BSTU International',
                        'greeting' => '{campaign_title}',
                        'intro' => '{campaign_message}',
                        'action_label' => '{campaign_cta_label}',
                        'expiry_notice' => '',
                        'no_action_notice' => 'Спасибо, что остаетесь на связи с BSTU International.',
                        'salutation' => 'С уважением,',
                        'signature' => 'Команда BSTU International',
                        'subcopy' => 'Это сообщение отправлено активным подписчикам рассылки.',
                        'footer' => '© {year} BSTU International. Все права защищены.',
                    ],
                    'ar' => [
                        'subject' => '{campaign_subject}',
                        'brand_name' => 'BSTU International',
                        'greeting' => '{campaign_title}',
                        'intro' => '{campaign_message}',
                        'action_label' => '{campaign_cta_label}',
                        'expiry_notice' => '',
                        'no_action_notice' => 'شكرا لبقائك على تواصل مع BSTU International.',
                        'salutation' => 'مع التحية،',
                        'signature' => 'فريق BSTU International',
                        'subcopy' => 'تم إرسال هذه الرسالة إلى المشتركين النشطين في النشرة الإخبارية.',
                        'footer' => '© {year} BSTU International. جميع الحقوق محفوظة.',
                    ],
                ]),
            ],
        ];
    }

    protected function localizedEmailTemplate(array $translations): array
    {
        return $translations;
    }
}

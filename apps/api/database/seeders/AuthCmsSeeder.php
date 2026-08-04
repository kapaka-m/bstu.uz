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
        ];
    }
}

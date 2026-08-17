<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudentPortalTranslationCompletenessSeeder extends Seeder
{
    public function run(): void
    {
        $translations = [
            'status.action_required' => [
                'en' => 'Action required',
                'uz' => 'Amal talab qilinadi',
                'ru' => 'Требуется действие',
                'ar' => 'إجراء مطلوب',
            ],
            'status.documents_required' => [
                'en' => 'Documents required',
                'uz' => 'Hujjatlar talab qilinadi',
                'ru' => 'Требуются документы',
                'ar' => 'المستندات مطلوبة',
            ],
            'status.application_fee_required' => [
                'en' => 'Application fee required',
                'uz' => 'Ariza to‘lovi talab qilinadi',
                'ru' => 'Требуется оплата заявки',
                'ar' => 'رسوم الطلب مطلوبة',
            ],
            'status.reupload_required' => [
                'en' => 'Re-upload required',
                'uz' => 'Qayta yuklash talab qilinadi',
                'ru' => 'Требуется повторная загрузка',
                'ar' => 'إعادة الرفع مطلوبة',
            ],
            'status.application_rejected' => [
                'en' => 'Application rejected',
                'uz' => 'Ariza rad etildi',
                'ru' => 'Заявка отклонена',
                'ar' => 'تم رفض الطلب',
            ],
            'status.admission_issued' => [
                'en' => 'Admission issued',
                'uz' => 'Qabul hujjati berildi',
                'ru' => 'Приказ о приеме выдан',
                'ar' => 'تم إصدار القبول',
            ],
            'status.result_issued' => [
                'en' => 'Result issued',
                'uz' => 'Natija berildi',
                'ru' => 'Результат выдан',
                'ar' => 'تم إصدار النتيجة',
            ],
            'status.student_review_required' => [
                'en' => 'Student review required',
                'uz' => 'Talaba tekshiruvi talab qilinadi',
                'ru' => 'Требуется проверка студентом',
                'ar' => 'مراجعة الطالب مطلوبة',
            ],
            'status.not_started' => [
                'en' => 'Not started',
                'uz' => 'Boshlanmagan',
                'ru' => 'Не начато',
                'ar' => 'لم يبدأ',
            ],
            'status.waiting_documents' => [
                'en' => 'Waiting for documents',
                'uz' => 'Hujjatlar kutilmoqda',
                'ru' => 'Ожидаются документы',
                'ar' => 'بانتظار المستندات',
            ],
            'status.waiting_payment' => [
                'en' => 'Waiting for payment',
                'uz' => 'To‘lov kutilmoqda',
                'ru' => 'Ожидается оплата',
                'ar' => 'بانتظار الدفع',
            ],
            'status.pending_review' => [
                'en' => 'Pending review',
                'uz' => 'Ko‘rib chiqilmoqda',
                'ru' => 'Ожидает проверки',
                'ar' => 'قيد المراجعة',
            ],
            'status.pending_verification' => [
                'en' => 'Pending verification',
                'uz' => 'Tasdiqlash kutilmoqda',
                'ru' => 'Ожидает подтверждения',
                'ar' => 'بانتظار التحقق',
            ],
            'status.verified' => [
                'en' => 'Verified',
                'uz' => 'Tasdiqlandi',
                'ru' => 'Проверено',
                'ar' => 'تم التحقق',
            ],
            'status.open' => [
                'en' => 'Open',
                'uz' => 'Ochiq',
                'ru' => 'Открыто',
                'ar' => 'مفتوح',
            ],
            'status.closed' => [
                'en' => 'Closed',
                'uz' => 'Yopiq',
                'ru' => 'Закрыто',
                'ar' => 'مغلق',
            ],
            'support.messagesTitle' => [
                'en' => 'Messages and Support',
                'uz' => 'Xabarlar va qo‘llab-quvvatlash',
                'ru' => 'Сообщения и поддержка',
                'ar' => 'الرسائل والدعم',
            ],
            'support.messagesSubtitle' => [
                'en' => 'Communicate with the university administration and follow every request in one place.',
                'uz' => 'Universitet ma’muriyati bilan muloqot qiling va barcha murojaatlarni bir joyda kuzating.',
                'ru' => 'Общайтесь с администрацией университета и отслеживайте все обращения в одном месте.',
                'ar' => 'تواصل مع إدارة الجامعة وتابع كل طلباتك من مكان واحد.',
            ],
            'support.adminCommunication' => [
                'en' => 'Student and administration communication',
                'uz' => 'Talaba va ma’muriyat muloqoti',
                'ru' => 'Связь студента с администрацией',
                'ar' => 'تواصل الطالب مع الإدارة',
            ],
            'support.conversation' => [
                'en' => 'Conversation',
                'uz' => 'Suhbat',
                'ru' => 'Переписка',
                'ar' => 'المحادثة',
            ],
            'support.fromStudent' => [
                'en' => 'Student',
                'uz' => 'Talaba',
                'ru' => 'Студент',
                'ar' => 'الطالب',
            ],
            'support.fromAdministration' => [
                'en' => 'Administration',
                'uz' => 'Ma’muriyat',
                'ru' => 'Администрация',
                'ar' => 'الإدارة',
            ],
            'support.noMessages' => [
                'en' => 'No messages yet.',
                'uz' => 'Hozircha xabarlar yo‘q.',
                'ru' => 'Сообщений пока нет.',
                'ar' => 'لا توجد رسائل حتى الآن.',
            ],
            'support.replyPlaceholder' => [
                'en' => 'Write a reply to the administration...',
                'uz' => 'Ma’muriyatga javob yozing...',
                'ru' => 'Напишите ответ администрации...',
                'ar' => 'اكتب ردا إلى الإدارة...',
            ],
            'support.sendReply' => [
                'en' => 'Send reply',
                'uz' => 'Javob yuborish',
                'ru' => 'Отправить ответ',
                'ar' => 'إرسال الرد',
            ],
            'support.replySent' => [
                'en' => 'Message sent successfully.',
                'uz' => 'Xabar muvaffaqiyatli yuborildi.',
                'ru' => 'Сообщение успешно отправлено.',
                'ar' => 'تم إرسال الرسالة بنجاح.',
            ],
            'support.replyFailed' => [
                'en' => 'Failed to send message.',
                'uz' => 'Xabarni yuborib bo‘lmadi.',
                'ru' => 'Не удалось отправить сообщение.',
                'ar' => 'تعذر إرسال الرسالة.',
            ],
        ];

        foreach ($translations as $fullKey => $values) {
            [$group, $key] = explode('.', $fullKey, 2);

            $keyId = DB::table('translation_keys')
                ->where('group', $group)
                ->where('key', $key)
                ->value('id');

            if (! $keyId) {
                $keyId = DB::table('translation_keys')->insertGetId([
                    'group' => $group,
                    'key' => $key,
                    'description' => 'Student portal dynamic UI status: '.$fullKey,
                    'is_system' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($values as $locale => $value) {
                DB::table('translation_values')->updateOrInsert(
                    ['translation_key_id' => $keyId, 'locale' => $locale],
                    ['value' => $value, 'updated_at' => now(), 'created_at' => now()],
                );
            }
        }
    }
}

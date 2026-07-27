# المراجعة النهائية للبيانات الثابتة في Laravel API

تاريخ المراجعة: 2026-07-27

## النتيجة

تمت مراجعة المسارات المطلوبة فقط. لا توجد بيانات أعمال ثابتة مهمة متبقية داخل هذه المسارات تحتاج النقل إلى قاعدة البيانات بعد التعديلات الأخيرة.

البيانات والنصوص الثابتة التي تصلح للإدارة من لوحة التحكم أصبحت الآن في قاعدة البيانات، ويمكن التحكم بها من apanel عبر موارد الإعدادات والجداول المناسبة.

## البيانات التي أصبحت في قاعدة البيانات

- جدول `settings`: إعدادات الطلب، الرسوم، العملة، نسبة دفعة العقد، رسائل خطوات workflow، نصوص PDF، تسميات جداول PDF، وأسماء الشهور الأوزبكية المستخدمة في ملفات PDF.
- جدول `application_countries`: الدول الخاصة بطلبات الطلاب.
- جدول `application_nationalities`: الجنسيات الخاصة بطلبات الطلاب.
- جدول `document_requirements`: متطلبات مستندات التقديم حسب الدرجة ونوع الطالب.

## مفاتيح PDF الجديدة داخل جدول settings

- `pdf.shared.uzbek_months`
- `pdf.admission.labels`
- `pdf.enrollment.labels`
- `pdf.prikaz.labels`
- `pdf.study_contract.labels`

## ملاحظة مهمة

بقيت داخل الكود أشياء تقنية طبيعية لا تعتبر بيانات محتوى لإدارتها من apanel، مثل:

- قواعد validation.
- أسماء الحقول داخل Models.
- أسماء status المستخدمة في workflow logic.
- إعدادات Laravel التقنية داخل `config`.
- ملفات cache/generated داخل `bootstrap/cache`.
- إعدادات VS Code المحلية.
- GitHub workflow files.

هذه الأشياء لا يجب نقلها لقاعدة البيانات لأنها تتحكم في تشغيل النظام وليست محتوى قابل للإدارة.

## المسارات التي تمت مراجعتها والتأكد منها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\app`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\config`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\bootstrap`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\.github`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\.vscode`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\.agents`

## ملفات تم تعديلها ضمن هذه المراجعة

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\app\Services\AdmissionPdfService.php`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\app\Services\EnrollmentCertificatePdfService.php`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\app\Services\PrikazPdfService.php`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\app\Services\StudyContractPdfService.php`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\app\Http\Middleware\LocaleMiddleware.php`

## ملفات قاعدة البيانات الداعمة التي تم تحديثها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\seeders\WorkflowConfigurationSeeder.php`

## التحقق

- تم تشغيل seeder: `WorkflowConfigurationSeeder`.
- تم التأكد من إضافة مفاتيح PDF الجديدة إلى جدول `settings`.
- تم فحص PHP syntax للملفات المعدلة ولا توجد أخطاء syntax.
- تم تقليل تسجيل raw request body في `LocaleMiddleware` ليعمل فقط عند `APP_DEBUG=true` حتى لا يتم تسجيل بيانات حساسة في الإنتاج.

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

---

# مراجعة إضافية لمسارات resources و public و node_modules

تاريخ المراجعة: 2026-07-27

## المسارات التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\resources`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\public`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\node_modules`

## النتيجة

لا توجد بيانات أعمال ثابتة في `resources` أو `public` تحتاج النقل إلى قاعدة البيانات.

تم حذف الملف التالي لأنه صفحة Laravel الافتراضية وغير مستخدم في نظام API:

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\resources\views\welcome.blade.php`

## الملفات المتبقية التي تم التأكد منها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\public\.htaccess`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\public\favicon.ico`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\public\index.php`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\public\robots.txt`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\resources\css\app.css`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\resources\js\app.js`

## node_modules

المسار التالي يحتوي مكتبات طرف ثالث مثبتة عن طريق npm، وليس مصدراً لبيانات المشروع:

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\node_modules`

لا يتم نقل محتوى `node_modules` إلى قاعدة البيانات ولا يتم تعديله يدوياً، لأنه يتم توليده من `package-lock.json` و `package.json`.

## التحقق

- تم البحث داخل `resources` و `public` عن بيانات ثابتة تخص الجامعة أو الطلاب أو البرامج أو الرسوم ولم يظهر شيء بعد حذف صفحة Laravel الافتراضية.
- تم تشغيل `route:list` بنجاح، وعدد routes بقي `170`.

---

# مراجعة إضافية لمسارات vendor و tests و storage و routes

تاريخ المراجعة: 2026-07-27

## المسارات التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\vendor`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\tests`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\routes`

## النتيجة

لا توجد بيانات أعمال ثابتة داخل هذه المسارات تحتاج النقل إلى قاعدة البيانات أو التحكم من apanel.

## تفاصيل القرار

- `vendor`: مكتبات Composer طرف ثالث، ولا يتم تعديلها أو نقل محتواها إلى قاعدة البيانات.
- `tests`: اختبارات فقط، وما فيها رسائل أو بيانات اختبارية لا تعتبر محتوى نظام production.
- `routes`: تعريفات API endpoints و middleware فقط، وليست بيانات محتوى.
- `storage`: ملفات runtime وليست كوداً ثابتاً. يحتوي uploads، صور CMS، PDFs مولدة، cache، logs، وملفات temp.

## تنظيم storage

لا يتم نقل ملفات `storage/app/public` أو `storage/app/private` إلى قاعدة البيانات، لأن قاعدة البيانات تحفظ المراجع والمسارات، أما الملفات نفسها يجب أن تبقى على disk.

تم تنظيف الملف التالي لأنه سجل runtime كبير وكان يحتوي سجلات طلبات قديمة:

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\logs\laravel.log`

لم يتم لمس ملفات الصور أو المستندات أو PDFs المولدة حتى لا تنكسر روابط قاعدة البيانات.

---

# تنظيم storage/app وربطه بقاعدة البيانات

تاريخ التنظيم: 2026-07-27

## المسارات التي تم التركيز عليها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app\private`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app\public`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app\.gitignore`

## الهيكل الجديد

### Public CMS Files

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app\public\cms\about-page`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app\public\cms\administration`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app\public\cms\blog`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app\public\cms\green-campus`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app\public\cms\media-library`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app\public\cms\news-events`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app\public\cms\university-centers`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app\public\cms\uploads\images`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app\public\cms\videos`

### Private Student Workflow Files

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app\private\applications\{application_id}\documents`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app\private\applications\{application_id}\receipts\application-fees`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app\private\applications\{application_id}\receipts\contract-payments`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app\private\applications\{application_id}\receipts\service-fees`

### Generated Private PDFs

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app\private\generated\admissions`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app\private\generated\contracts`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app\private\generated\enrollments`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app\private\generated\prikazes`

## ما تم تنفيذه

- تم نقل الملفات العامة من المسارات القديمة مثل `media`, `centers`, `news-events`, و `about-page` إلى `cms/...`.
- تم نقل ملفات `uploads/images/YYYY-MM-DD` إلى `cms/uploads/images/YYYY/MM/DD`.
- تم نقل وثائق الطلاب والإيصالات من `private/...` القديم إلى `applications/{application_id}/...`.
- تم نقل PDFs المولدة إلى `generated/...`.
- تم تحديث مسارات قاعدة البيانات القديمة إلى المسارات الجديدة.
- تم تعديل كود الرفع حتى أي ملفات جديدة تستخدم الهيكل الجديد مباشرة.
- تم ضبط روابط صور `university_centers` لتستخدم disk `public` صراحة بعد التنظيم.
- تم إضافة command للتنظيم: `php-local.bat artisan bstu:organize-storage`.

## التحقق

- لا توجد مسارات قديمة في قاعدة البيانات من نوع `private/%` للوثائق والإيصالات.
- لا توجد مسارات قديمة في قاعدة البيانات من نوع `admissions/%`, `contracts/%`, `enrollments/%`, `prikazes/%`.
- لا توجد مسارات CMS قديمة في قاعدة البيانات من نوع `media/%`, `about-page/%`, `centers/%`, `news-events/%` ضمن الجداول التي تم تنظيمها.
- تم تشغيل `php-local.bat artisan test` بنجاح: 5 tests passed.
- تم تشغيل `route:list` بنجاح: 170 routes.

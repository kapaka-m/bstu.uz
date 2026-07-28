# المراجعة النهائية للبيانات الثابتة في Laravel API

تاريخ المراجعة: 2026-07-27

## نتيجة مراجعة Laravel API

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

## تحقق مراجعة Laravel API

- تم تشغيل seeder: `WorkflowConfigurationSeeder`.
- تم التأكد من إضافة مفاتيح PDF الجديدة إلى جدول `settings`.
- تم فحص PHP syntax للملفات المعدلة ولا توجد أخطاء syntax.
- تم تقليل تسجيل raw request body في `LocaleMiddleware` ليعمل فقط عند `APP_DEBUG=true` حتى لا يتم تسجيل بيانات حساسة في الإنتاج.

---

## مراجعة إضافية لمسارات resources و public و node_modules

تاريخ المراجعة: 2026-07-27

## المسارات التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\resources`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\public`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\node_modules`

### النتيجة لمسارات Vendor والـ Routes

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

### التحقق لمسارات Resources و Public

- تم البحث داخل `resources` و `public` عن بيانات ثابتة تخص الجامعة أو الطلاب أو البرامج أو الرسوم ولم يظهر شيء بعد حذف صفحة Laravel الافتراضية.
- تم تشغيل `route:list` بنجاح، وعدد routes بقي `170`.

---

## مراجعة إضافية لمسارات vendor و tests و storage و routes

تاريخ المراجعة: 2026-07-27

### المسارات التي تمت مراجعتها للـ Vendor والـ Routes

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\vendor`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\tests`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\routes`

### النتيجة لمسارات Web Public Assets

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

## تنظيم storage/app وربطه بقاعدة البيانات

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

### التحقق لمسارات Storage والـ Routes

- لا توجد مسارات قديمة في قاعدة البيانات من نوع `private/%` للوثائق والإيصالات.
- لا توجد مسارات قديمة في قاعدة البيانات من نوع `admissions/%`, `contracts/%`, `enrollments/%`, `prikazes/%`.
- لا توجد مسارات CMS قديمة في قاعدة البيانات من نوع `media/%`, `about-page/%`, `centers/%`, `news-events/%` ضمن الجداول التي تم تنظيمها.
- تم تشغيل `php-local.bat artisan test` بنجاح: 5 tests passed.
- تم تشغيل `route:list` بنجاح: 170 routes.

---

## مراجعة ملفات جذر Laravel API

تاريخ المراجعة: 2026-07-27

## الملفات التي تمت مراجعتها لجذر API

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\.editorconfig`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\.env`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\.env.example`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\.gitattributes`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\.gitignore`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\.npmrc`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\.styleci.yml`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\artisan`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\composer.json`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\composer.lock`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\package-lock.json`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\package.json`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\php-local.bat`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\php.ini`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\phpunit.xml`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\serve-local.bat`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\vite.config.js`

## النتيجة لمراجعة ملفات جذر API

لا توجد بيانات أعمال ثابتة داخل هذه الملفات تحتاج النقل إلى قاعدة البيانات أو التحكم من apanel.

هذه الملفات هي إعدادات تشغيل، dependency manifests، lockfiles، أدوات local PHP، إعدادات اختبار، وإعدادات Vite/Composer/NPM. لذلك مكانها الطبيعي داخل `apps/api`.

## ما تم تنظيمه

- تم تحديث `composer.json` من metadata الافتراضية الخاصة بـ Laravel skeleton إلى metadata خاصة بـ BSTU International API.
- تم تحديث `composer.lock` بعد تعديل metadata.
- تم إزالة مسار Windows المطلق من `php.ini` واستبداله بمسار نسبي: `storage/temp`.
- تم تعديل `serve-local.bat` ليستخدم `-t public` مع بقاء working directory داخل `apps/api` حتى يعمل مسار `storage/temp` النسبي بشكل صحيح.

## ملاحظات

- ملف `.env` يحتوي إعدادات بيئة محلية مثل `APP_KEY`, DB, mail, cache. هذه لا تنقل لقاعدة البيانات ولا apanel، ويجب أن تبقى ignored.
- ملف `.env.example` قالب إعدادات للمطورين وليس بيانات production.
- `composer.lock` و `package-lock.json` لا يتم نقلهم أو تحريرهم يدوياً لأنهم يثبتون إصدارات الحزم.

### التحقق لمسارات Root API

- `php-local.bat -r "echo sys_get_temp_dir().PHP_EOL;"` أرجع `storage/temp`.
- `composer validate --no-check-publish` نجح مع تحذير واحد فقط عن exact TCPDF version.
- `php-local.bat artisan test` نجح: 5 tests passed.
- `route:list` نجح: 170 routes.

---

## مراجعة Web Public Assets

تاريخ المراجعة: 2026-07-27

### المسارات التي تمت مراجعتها لـ Web Public Assets

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\public`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\public\robots.txt`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\public\assets`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\public\assets\img`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\public\assets\video`

### ما تم تنظيمه لـ Web Public Assets

- تم نقل صور الأشخاص التي كانت مستخدمة كبيانات محتوى من `apps/web/public/assets/img/...` إلى `apps/api/storage/app/public/cms/staff/...`.
- تم تحديث ملفات seed/data حتى تستخدم مسارات `cms/staff/...` بدلا من `/assets/img/...`.
- تم التأكد أن قاعدة البيانات الحالية لا تحتوي أي قيم `assets/img`.
- تم حذف مجلد `assets/video` لأنه فارغ.
- تم حذف مجلدات وصور public القديمة غير المستخدمة بعد نقل مراجع المحتوى.
- تم حذف مكون React غير مستخدم كان يشير إلى صور `clients` غير موجودة.

## الملفات المتبقية في Web Public

هذه ملفات واجهة ثابتة طبيعية متبقية في Web public:

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\public\robots.txt`

## النتيجة لمراجعة Web Public Assets

المسار `apps/web/public` أصبح مرتب: يحتوي فقط `robots.txt`. صور المحتوى، ملفات branding، وصور hero التي يجب التحكم بها من النظام أصبحت في Laravel storage وتقرأ عبر API/apanel.

## تحديث Branding Assets

تم نقل ملفات favicon والشعارات من `apps/web/public/assets/img` إلى:

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app\public\cms\branding`

وتم ربطها بجدول `settings` كمفاتيح عامة قابلة للتحكم من apanel:

- `branding_logo_default`
- `branding_logo_en`
- `branding_logo_ru`
- `branding_logo_ar`
- `branding_favicon_ico`
- `branding_favicon_png`
- `branding_favicon_32`
- `branding_favicon_16`
- `branding_apple_touch_icon`
- `branding_android_chrome_512`
- `branding_android_chrome_192`

كما تم إضافة سجلات لها في جدول `media` حتى تظهر في Media Picker داخل apanel.

## تحقق Branding Assets

- عدد إعدادات branding في جدول `settings`: 11.
- عدد سجلات branding في جدول `media`: 11.
- لا توجد ملفات branding مفقودة من `storage/app/public/cms/branding`.
- لا توجد مراجع قديمة في الواجهة إلى `/assets/img/favicon...` أو `/assets/img/bstu...`.

## تحديث Home Hero Assets

تم نقل صور hero من `apps/web/public/assets/img` إلى:

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app\public\cms\home\hero`

وتم ربطها بجدول `settings` كمفاتيح عامة قابلة للتحكم من apanel:

- `home_hero_background_image`
- `home_hero_main_image`

كما تم إضافة سجلات لها في جدول `media` حتى تظهر في Media Picker داخل apanel. بعد ذلك تم حذف `apps/web/public/assets` لأنه أصبح فارغا.

---

## مراجعة ملفات جذر React Web

تاريخ المراجعة: 2026-07-27

## الملفات التي تمت مراجعتها لجذر React

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\.env`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\.env.example`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\eslint.config.js`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\index.html`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\package.json`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\package-lock.json`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\docs\WEB_FRONTEND_README.md` (منقول من `apps\web\README.md`)
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\tailwind.config.js`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\vite.config.js`

### ما تم تنظيمه لملفات جذر React

- تم تنظيف `index.html` من meta keywords القديمة غير المناسبة.
- تم إضافة إعدادات عامة في قاعدة البيانات للتحكم في:
  - `site_name`
  - `site_meta_description`
  - `site_meta_keywords`
- تم تعديل واجهة React لتحدث `document.title` و meta description و meta keywords من `/settings/public`.
- تم نقل `apps/web/README.md` إلى `docs/WEB_FRONTEND_README.md`.
- تم تحديث `docs/WEB_FRONTEND_README.md` لأن `public/assets` لم يعد يحتوي favicons/logos بعد نقلها إلى Laravel storage.
- تم إضافة رابط `Web Frontend Notes` في فهرس التوثيق داخل `README.md` الرئيسي.

## النتيجة لمراجعة ملفات جذر React

- `vite.config.js`, `tailwind.config.js`, و `eslint.config.js` إعدادات بناء وتصميم وفحص فقط، ولا تحتوي بيانات أعمال تحتاج قاعدة البيانات.
- `package.json` و `package-lock.json` ملفات dependencies/scripts ولا تنقل إلى قاعدة البيانات.
- `.env` يحتوي قيم `VITE_*` عامة للواجهة فقط، وهو ignored بواسطة `.gitignore`.
- `.env.example` قالب للمطورين وليس بيانات production.
- `index.html` يحتوي fallback أولي فقط، والقيم القابلة للتغيير أصبحت من API/settings.

### التحقق لملفات جذر React

- `npm.cmd run lint` نجح.
- `php-local.bat artisan test` نجح: 5 tests passed.
- إعدادات SEO العامة موجودة في جدول `settings` ومكشوفة كـ public settings.

## ملاحظة منفصلة

أثناء الفحص ظهر أن هناك مسارات media أخرى محفوظة في قاعدة البيانات مثل `faculties/...`, `departments/...`, `programs/...`, وبعض `staff/...` لا توجد ملفاتها حاليا في `storage/app/public`. هذه ليست مراجع إلى `apps/web/public/assets` وليست من الملفات التي تم حذفها في هذه المراجعة، لكنها تحتاج جولة تنظيم منفصلة لمسارات Laravel storage العامة.

---

## مراجعة Legacy React Import Data

تاريخ المراجعة: 2026-07-27

## الملفات التي تمت مراجعتها ثم حذفها بعد النقل

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\scripts\import-react-content\legacy-react-data\departmentsData.js`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\scripts\import-react-content\legacy-react-data\facultyTechnology.js`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\scripts\import-react-content\legacy-react-data\mockData.js`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\scripts\import-react-content\legacy-react-data\README.md`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\scripts\import-react-content\import.js`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\scripts`

## نتيجة Legacy React Import Data

- تم دمج محتوى `departmentsData.js` في `apps/api/database/data/departments.json` بدون إنشاء أقسام مكررة.
- تم نقل محتوى `mockData.js` إلى `apps/api/database/data/academic_department_details.json` داخل `legacy_reviewed_sources.mockData`.
- تم نقل محتوى `facultyTechnology.js` إلى `apps/api/database/data/academic_department_details.json` داخل `legacy_reviewed_sources.facultyTechnology`.
- تم تفريغ ملفات legacy الثلاثة بعد النقل وتركها كـ compatibility markers فقط.
- بعد التأكد من نقل المحتوى، تم حذف مجلد `scripts` لأنه لم يعد يحتوي أدوات مستخدمة.
- تم تحويل مسارات الصور القديمة التي لم تعد موجودة إلى `legacy_removed_web_asset:...` داخل الأرشيف المنقول، بينما مسارات التشغيل في `departments.json` تستخدم `cms/staff/...`.

## مصدر الحقيقة الحالي

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\data\academic_department_details.json`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\data\departments.json`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\data\programs.json`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\data\translations.json`

## تحقق Legacy React Import Data

- ملفات JSON الأربعة صالحة.
- لا توجد مسارات تشغيل تبدأ بـ `/assets/img` في ملفات `apps/api/database/data`.
- `npm.cmd run lint` نجح.
- `php-local.bat artisan test` نجح: 5 tests passed.

---

## مراجعة مكونات React المشتركة

تاريخ المراجعة: 2026-07-28

## ملفات مكونات React المشتركة التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\ScrollToTop.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\PageHeader.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\Header.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\Footer.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\common\EmptyState.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\common\ErrorBoundary.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\common\ErrorState.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\common\FieldError.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\common\FormError.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\common\LoadingState.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\common\Pagination.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\common\RetryButton.jsx`

## ما تم نقله/ربطه بقاعدة البيانات

- `Header.jsx`: القائمة واللغات والشعار كانت بالفعل ديناميكية من API/settings/locales/menu. تم تنظيف أسماء اللغات الاحتياطية وربطها بمفاتيح ترجمة قابلة للإدارة.
- `Footer.jsx`: المحتوى كان بالفعل ديناميكي من `footerService` ومربوط بإدارة الفوتر داخل apanel.
- `PageHeader.jsx`: تم تحويل رابط `Home` إلى `nav.home` من جدول الترجمات.
- ملفات `common`: تم تحويل نصوص حالات الفراغ والتحميل والخطأ وزر إعادة المحاولة والترقيم إلى مفاتيح ترجمة داخل `apps/api/database/data/translations.json`.

## مفاتيح الترجمة المضافة

- `common.emptyTitle`
- `common.emptyMessage`
- `common.errorTitle`
- `common.errorMessage`
- `common.pageRenderErrorRetry`
- `common.loading`
- `common.retryConnection`
- `common.previous`
- `common.next`
- `common.paginationStatus`
- `common.localeEnglish`
- `common.localeUzbek`
- `common.localeRussian`
- `common.localeArabic`

## التنظيم والتنظيف

- تم استبدال SVG اليدوي داخل `EmptyState`, `ErrorState`, و `FormError` بأيقونات `lucide-react`.
- تم تحسين `PageHeader` لدعم اتجاه سهم breadcrumbs في العربية.
- تم إضافة `type="button"` لزر dropdown في الهيدر.
- لم يتم إنشاء جدول جديد لأن جدول `translation_keys` و `translation_values` موجود بالفعل وتديره صفحة `apanel/translations`.

## نتيجة مكونات React المشتركة

- لا توجد بيانات أعمال ثابتة داخل هذه المكونات تحتاج جدولاً منفصلاً.
- النصوص العامة القابلة للتغيير أصبحت ضمن نظام الترجمات، ويمكن إدارتها من apanel عبر Translation Keys / Translation Values.
- القيم التقنية الباقية داخل الملفات هي كلاسات CSS، مفاتيح داخلية، ومسارات routing/حالات UI وليست بيانات محتوى.

## تحقق مكونات React المشتركة

- `npm.cmd run lint` نجح بدون أخطاء.
- `php-local.bat artisan test` نجح: 5 tests passed.
- تم التأكد أن مفاتيح الترجمة الجديدة موجودة لكل اللغات: `en`, `uz`, `ru`, `ar`.
- تم تشغيل `TranslationKeySeeder` و `TranslationValueSeeder` لإدخال/تحديث المفاتيح والقيم في قاعدة البيانات بدون حذف بيانات موجودة.
- تم تشغيل `php-local.bat artisan cache:clear` حتى تظهر الترجمات العامة الجديدة من API.

---

## مراجعة React Context ومصدر بيانات المكونات

تاريخ المراجعة: 2026-07-28

## ملفات React Context التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\context\LocaleContext.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\context\LanguageContext.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\context\AuthContext.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\context\AppDataContext.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\context`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components`

## ما تم تأكيده وتعديله

- `LocaleContext.jsx` يجلب اللغات من `/api/v1/locales`.
- `LocaleContext.jsx` يجلب الترجمات من `/api/v1/translations`.
- `LocaleContext.jsx` يجلب الإعدادات العامة والشعار و favicon و meta من `/api/v1/settings/public`.
- `LocaleContext.jsx` يجلب قائمة الهيدر من `/api/v1/menus/header`.
- تم حذف الاعتماد على `fallbackTranslations` المحلي داخل `LocaleContext.jsx`.
- تم حذف fallback المحلي لمسارات `cms/branding/...` داخل `LocaleContext.jsx`; الشعار و favicon الآن من جدول `settings` عبر API.
- `Header.jsx` لم يعد يحتوي fallback مرئي لقائمة اللغات أو زر الدخول. اللغات تأتي من جدول `locales`، وزر الدخول يأتي من قائمة الهيدر في قاعدة البيانات.
- `AuthContext.jsx` يستخدم `authService` فقط: login/register/logout/current user عبر API، وبيانات المستخدم/الأدوار من قاعدة البيانات.
- `AppDataContext.jsx` يستخدم API فقط للكليات، الأقسام، البرامج، الخدمات، الفيديوهات، و Green Campus.

## نتيجة React Context ومصدر البيانات

- لا توجد بيانات محتوى static داخل `context` أو `components`.
- البيانات المعروضة أو المستخدمة كمحتوى تأتي من قاعدة البيانات عبر API.
- المتبقي داخل هذه الملفات هو كود تقني طبيعي فقط: مفاتيح إعداد الاتصال، أسماء دوال، مفاتيح داخلية، معالجة أخطاء للمطورين، وحالات React.

## تحقق React Context ومصدر البيانات

- لا يوجد استخدام لـ `fallbackTranslations` داخل `apps/web/src/context` أو `apps/web/src/components`.
- لا توجد مسارات branding محلية مثل `cms/branding/...` داخل `apps/web/src/context` أو `apps/web/src/components`.
- `npm.cmd run lint` نجح.
- `php-local.bat artisan test` نجح: 5 tests passed.

---

## مراجعة مجلد React Data

تاريخ المراجعة: 2026-07-28

## ملفات React Data التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\data`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\docs\frontend_data_folder.md` (منقول من `apps\web\src\data\README.md`)
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\data\translations.js`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\data\fallbackTranslations`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\data\fallbackTranslations\ar.js`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\data\fallbackTranslations\en.js`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\data\fallbackTranslations\ru.js`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\data\fallbackTranslations\uz.js`

## ما تم تنظيفه في React Data

- تم حذف `translations.js` لأنه كان يحتوي نسخة static محلية ولم يعد مستخدماً بعد اعتماد `LocaleContext.jsx` على API فقط.
- تم حذف ملفات `fallbackTranslations/*.js` لأنها كانت نسخ fallback محلية ولم تعد مستخدمة في التشغيل.
- تم نقل `apps/web/src/data/README.md` إلى `docs/frontend_data_folder.md`.
- تم تحديث التوثيق ليؤكد أن مجلد `src/data` ليس مصدر محتوى، وأن مصدر الحقيقة هو Laravel API/MySQL و `apps/api/database/data`.

## نتيجة React Data

- لا توجد بيانات ترجمة أو محتوى static مستخدمة من `apps/web/src/data`.
- الترجمات الحالية تأتي من جداول `translation_keys` و `translation_values` عبر `/api/v1/translations`.
- المحتوى العام واللغات والإعدادات تأتي من قاعدة البيانات عبر API.

## تحقق React Data

- لا يوجد import أو reference لـ `fallbackTranslations` أو `translations.js` داخل `apps/web/src` بعد الحذف.
- `npm.cmd run lint` نجح.

---

## مراجعة React Lib

تاريخ المراجعة: 2026-07-28

## ملفات React Lib التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\lib`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\lib\api.js`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\lib\auth.js`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\lib\locale.js`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\lib\storage.js`

## ما تم تأكيده وتعديله في React Lib

- `api.js` هو طبقة اتصال فقط ويستخدم `VITE_API_BASE_URL` للاتصال بالـ Laravel API.
- `api.js` لم يعد يضيف `locale` للطلبات إلا بعد توفر اللغة المختارة من التخزين/جدول `locales`.
- `locale.js` لم يعد يحتوي لغة افتراضية hardcoded مثل `en`; اللغة الأولى تأتي من `/api/v1/locales` عبر `LocaleContext.jsx`.
- `auth.js` يخزن token وبيانات المستخدم التي ترجع من API فقط.
- `storage.js` طبقة تقنية للتعامل مع `localStorage` ولا يحتوي محتوى أو بيانات أعمال.

## نتيجة React Lib

- لا توجد بيانات محتوى static داخل ملفات `apps/web/src/lib`.
- البيانات التي تتحكم في اللغة والمستخدم والمحتوى تأتي من API وقاعدة البيانات.
- المتبقي داخل هذه الملفات هو مفاتيح تخزين وإعدادات اتصال وأسماء headers وهي تفاصيل تشغيل تقنية لا تدار من apanel.

## تحقق React Lib

- `npm.cmd run lint` نجح بدون أخطاء أو تحذيرات.
- تم التأكد من وجود routes العامة: `/api/v1/locales`, `/api/v1/translations`, `/api/v1/settings/public`.

---

## مراجعة React Services

تاريخ المراجعة: 2026-07-28

## ملفات React Services التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\services`
- `aboutService.js`
- `administrationService.js`
- `announcementService.js`
- `apanelApplicationsService.js`
- `apanelService.js`
- `applicationService.js`
- `authService.js`
- `blogService.js`
- `centerService.js`
- `commentService.js`
- `contactService.js`
- `departmentService.js`
- `facultyService.js`
- `footerService.js`
- `greenCampusService.js`
- `initialApplicationService.js`
- `inquiryService.js`
- `menuService.js`
- `newsService.js`
- `notificationService.js`
- `pageService.js`
- `programService.js`
- `serviceService.js`
- `staffService.js`
- `studentPortalService.js`
- `studentService.js`
- `translationService.js`
- `videoService.js`

## ما تم تأكيده وتعديله في React Services

- جميع الخدمات تعمل كطبقة API client ولا تحتوي مصدر محتوى محلي.
- تمت إزالة استخدام `VITE_API_BASE_URL` المباشر من services، وأصبح تنزيل الملفات والروابط العامة عبر helpers في `lib/api.js`.
- `apanelApplicationsService.js` و `studentPortalService.js` يستخدمان `downloadBlob` من `lib/api.js` لتنزيل الملفات الآمنة.
- `greenCampusService.js` يستخدم `publicAssetUrl` من `lib/api.js` لبناء روابط الملفات العامة.
- `blogService.js` لم يعد يحتوي أسماء شهور أوزبكية ثابتة؛ تنسيق التاريخ يستخدم `Intl.DateTimeFormat` حسب اللغة الحالية.
- تمت إزالة `console.error` من `apanelService.js` في fallback dashboard، لأن الواجهة تتعامل مع النتيجة بدون محتوى ثابت.

## نتيجة React Services

- لا توجد بيانات محتوى static داخل `apps/web/src/services`.
- النصوص، الإعدادات، القوائم، المستخدم، الفوتر، الصفحات، الملفات، وبيانات CMS تأتي من Laravel API والجداول المرتبطة بها.
- المسارات الموجودة داخل الخدمات هي endpoints تقنية وليست بيانات محتوى قابلة للإدارة من apanel.

## تحقق React Services

- `npm.cmd run lint` نجح بدون أخطاء أو تحذيرات.
- `php-local.bat artisan test` نجح: 5 tests passed.
- `php-local.bat artisan route:list --path=api/v1 --except-vendor` نجح وأظهر 170 route.

---

## مراجعة Laravel Routes Storage Tests Vendor

تاريخ المراجعة: 2026-07-28

## ملفات Laravel Routes Storage Tests Vendor التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\routes`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\routes\api.php`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\routes\web.php`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\routes\console.php`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app\private`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\app\public`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\framework`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\logs`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\temp`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\tests`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\vendor`

## ما تم العثور عليه في Laravel Routes Storage Tests Vendor

- `routes/api.php` يستخدم `/api/v1`, `/student/*`, و`/apanel/*` فقط لإدارة لوحة التحكم، ولا يحتوي route باسم `/admin`.
- public website routes تعتمد على `PublicApiController` وجداول CMS الحالية.
- student portal routes تعتمد على `StudentApiController` و`StudentApplicationPortalController`.
- apanel routes تعتمد على `AdminCrudController` و`ApanelApplicationWorkflowController`.
- `storage/app/public/cms` يحتوي ملفات CMS منظمة حسب المجالات مثل branding, news-events, blog, green-campus, staff, videos, media-library.
- `storage/app/private` يحتوي ملفات طلاب ووثائق ومدفوعات ومستندات PDF مولدة، وهي بيانات مستخدمين لا يجب حذفها أو نقلها بدون تحديث قاعدة البيانات.
- `storage/temp` و`storage/framework/views` احتويا ملفات مؤقتة مولدة من PHPUnit/Laravel.
- `vendor` حزم Composer طرف ثالث، وليست مصدر محتوى للمشروع.
- `tests/Feature/ExampleTest.php` و`tests/Unit/ExampleTest.php` كانا boilerplate افتراضيين من Laravel.
- `routes/console.php` كان يحتوي command `inspire` الافتراضي من Laravel.

## تغييرات Laravel Routes Storage Tests Vendor

- تمت إزالة command `inspire` الافتراضي من `routes/console.php`.
- تم حذف `tests/Unit/ExampleTest.php` لأنه اختبار demo لا يختبر سلوكاً حقيقياً.
- تم استبدال `tests/Feature/ExampleTest.php` بـ `tests/Feature/HealthRouteTest.php` لاختبار health route باسم واضح.
- تم تنظيف ملفات temp/compiled view المولدة داخل `storage/temp` و`storage/framework/views`، مع ترك ملفات `.gitignore`.
- لم يتم حذف أو نقل أي ملف طالب أو CMS من `storage/app/private` أو `storage/app/public/cms`.

## ربط Laravel Routes Storage Tests Vendor بالبيانات

- ملفات CMS العامة مربوطة بجداول مثل `media`, `news`, `blogs`, `staff_profiles`, `videos`, `settings`, وجداول CMS/translation الخاصة بها.
- ملفات الطلاب الخاصة مربوطة بجداول مثل `application_documents`, `application_fee_payments`, `service_fee_payments`, `contracts`, `admissions`, `enrollments`, و`prikazes`.
- تم التحقق من وجود سجلات قاعدة بيانات مرتبطة بهذه الأنواع: `media=17`, `application_documents=5`, `application_fee_payments=1`, `service_fee_payments=1`, `contracts=1`, `admissions=1`, `enrollments=1`, `prikazes=1`.
- API المستخدم للعرض والإدارة: `/api/v1/media/{id}`, `/api/v1/settings/public`, public CMS endpoints، `/api/v1/student/*`, و`/api/v1/apanel/*`.
- `/apanel/`: إدارة CMS من `/apanel/cms/*` وCRUD العام من `/apanel/{resource}`، وإدارة workflow/وثائق الطلاب من `/apanel/applications-workflow/*`.

## تحقق Laravel Routes Storage Tests Vendor

- إعادة المسح لم تجد `/admin`, `localhost`, `127.0.0.1`, `VITE_API_BASE_URL`, `mock`, `demo`, `ExampleTest`, أو `inspire` داخل `routes` و`tests`.
- تم فحص syntax للملفات المعدلة: `routes/api.php`, `routes/web.php`, `routes/console.php`, `tests/Feature/ApiSecurityTest.php`, `tests/Feature/HealthRouteTest.php`, و`tests/TestCase.php`.
- `php-local.bat artisan optimize:clear` نجح.
- `php-local.bat artisan route:list --path=api/v1 --except-vendor` نجح وأظهر 170 route.
- `php-local.bat artisan test` نجح: 4 tests passed, 7 assertions.
- `npm.cmd run lint` نجح.
- `npm.cmd run build` نجح.

## المتبقي في Laravel Routes Storage Tests Vendor

- `vendor` لم يتم تعديله لأنه dependency folder لطرف ثالث.
- `storage/logs/laravel.log` لم يتم حذفه لأنه ملف log تشخيصي وليس محتوى CMS.
- ملفات `storage/app/public/cms` و`storage/app/private` باقية كما هي لأنها بيانات وملفات فعلية مربوطة بقاعدة البيانات.

---

## مراجعة Laravel Public Root Environment Files

تاريخ المراجعة: 2026-07-28

## ملفات Laravel Public Root Environment التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\public`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\public\storage`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\public\storage\cms`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\public\robots.txt`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\public\index.php`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\public\favicon.ico`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\public\.htaccess`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\.editorconfig`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\.env`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\.env.example`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\.gitattributes`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\.gitignore`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\.npmrc`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\.phpunit.result.cache`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\.styleci.yml`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\artisan`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\composer.json`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\composer.lock`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\package.json`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\package-lock.json`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\php-local.bat`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\php.ini`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\phpunit.xml`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\serve-local.bat`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\vite.config.js`

## ما تم العثور عليه في Laravel Public Root Environment

- `public/storage` هو Junction إلى `storage/app/public` وليس نسخة مستقلة من ملفات CMS.
- `public/storage/cms/*` يعرض ملفات CMS المنظمة الموجودة في storage العام، وهي مربوطة بجداول CMS/media ولا يجب نقلها من هذا المسار.
- `public/favicon.ico` مطابق hash للملف `storage/app/public/cms/branding/favicon.ico`.
- `public/index.php`, `.htaccess`, و`robots.txt` ملفات front controller وتشغيل عامة ولا تحتوي محتوى CMS.
- `.env.example`, `serve-local.bat`, و`php.ini` كانت تحتوي قيم أو مسارات محلية ثابتة.
- `.env` يحتوي إعدادات بيئة الجهاز الحالية، وتم فحصه بنمط masked فقط بدون عرض القيم السرية أو تعديلها.
- `.phpunit.result.cache` ملف مولد من PHPUnit.

## تغييرات Laravel Public Root Environment

- تم تنظيف `.env.example` من `localhost`, `127.0.0.1`, اسم التطبيق الثابت، وقيم قاعدة البيانات المحلية.
- تم تعديل `serve-local.bat` لاستخدام `PHP_SERVER_HOST` و`PHP_SERVER_PORT` بدلاً من تثبيت `127.0.0.1:8000`.
- تم تعديل `php-local.bat` و`serve-local.bat` لاكتشاف `extension_dir` تلقائياً من مسار `php.exe` وإنشاء `storage/temp/php.ini` وقت التشغيل بدلاً من تخزين مسار Windows ثابت داخل `php.ini`.
- تم حذف `extension_dir = C:\Program Files\...` من `php.ini`.
- تم حذف `.phpunit.result.cache` لأنه cache مولد.
- تم تنظيف ملفات temp/compiled view التي أنشأتها الفحوصات بعد انتهاء الاختبارات.

## ربط Laravel Public Root Environment بالبيانات

- ملفات branding وCMS في `public/storage/cms/*` مرتبطة بمسارات التخزين في جداول `media`, `settings`, وجداول CMS الخاصة مثل footer/header/home/news/blog/videos/staff/centers.
- `public/favicon.ico` مطابق لنسخة branding الموجودة في storage، لكن إدارة favicon الفعلية تتم من مصدر CMS/branding وليس من كود React.
- API المستخدم لعرض ملفات ومحتوى CMS: `/api/v1/media/{id}`, `/api/v1/settings/public`, وpublic CMS endpoints.
- `/apanel/`: إدارة الملفات والمحتوى من `/apanel/cms/*`, `/apanel/{resource}`, وصفحات media/settings الحالية.

## تحقق Laravel Public Root Environment

- تم فحص `.env` بنمط masked فقط للتأكد من وجود إعدادات بيئة محلية دون كشف أسرار.
- إعادة المسح لم تجد `http://localhost`, `localhost:`, `127.0.0.1`, `VITE_API_BASE_URL`, `/admin`, `demo`, `TODO`, `FIXME`, `C:\Program Files`, أو `BSTU Platform` داخل ملفات هذه المجموعة باستثناء dependency اسمها `mockery/mockery`.
- `php-local.bat -l public/index.php` و`php-local.bat -l artisan` نجحا بدون تحذيرات extensions بعد تعديل السكربت.
- `php-local.bat artisan optimize:clear` نجح.
- `php-local.bat artisan route:list --path=api/v1 --except-vendor` نجح وأظهر 170 route.
- `php-local.bat artisan test` نجح: 4 tests passed, 7 assertions.
- `npm.cmd run lint` نجح.
- `npm.cmd run build` نجح.

## المتبقي في Laravel Public Root Environment

- `.env` بقي كما هو لأنه ملف بيئة محلي فعلي؛ لم يتم تحويله إلى قاعدة البيانات ولا تعديله حتى لا ينكسر تشغيل الجهاز.
- `public/favicon.ico` بقي لأنه root favicon تقني، ومطابق لنسخة branding الموجودة في storage.
- ملفات `composer.lock` و`package-lock.json` بقيت كما هي لأنها lockfiles وليست محتوى CMS.
- `public/storage` بقي Junction كما هو لأنه رابط Laravel العام إلى storage، وليس مجلد محتوى مستقل.

---

## مراجعة Laravel App Backend Dynamic Runtime

تاريخ المراجعة: 2026-07-28

## ملفات Laravel App Backend التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\app\Traits`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\app\Services`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\app\Providers`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\app\Models`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\app\Console\Commands`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\app\Http\Controllers`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\app\Http\Middleware`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\app\Http\Requests`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\app\Http\Resources`

## ما تم العثور عليه في Laravel App Backend

- كان `HasTranslations` و `PublicApiController` و `InitialApplicationController` يعتمدون على fallback ثابت للغة `en`.
- كان `PublicApiController` يحدد اتجاه اللغة بـ `ar => rtl` داخل الكود، وهذا يقفل النظام على لغة واحدة RTL.
- كانت خدمات PDF تحتوي عناوين وlabels وعبارات افتراضية داخل الكود مثل Admission Letter وStudent Name وشهور أوزبكية.
- كان `ApplicationWorkflowService` يحتوي أسماء خطوات workflow وحالات وإشعار إصدار القبول كنصوص مباشرة.
- كان إشعار الإعلان في `AdminCrudController` يحتوي fallback رابط واجهة محلي `http://localhost:5173`.
- بقية النصوص التي بقيت داخل هذه المجموعة هي نصوص تقنية: أسماء حالات قاعدة البيانات، قواعد validation، مفاتيح settings/translations، أسماء routes، أسماء storage folders، ورسائل أخطاء تقنية.

## تغييرات Laravel App Backend

- تم جعل fallback اللغة في `HasTranslations` من جدول `locales` حسب أول لغة نشطة مرتبة، مع `config` كحل تقني أخير فقط عند غياب جدول/بيانات اللغات.
- تم جعل `PublicApiController` يقرأ اللغات النشطة واتجاه اللغة من جدول `locales` بدلاً من قائمة ثابتة.
- تم تحويل fallback ترجمة المحتوى داخل `PublicApiController` و `InitialApplicationController` من `en` إلى fallback ديناميكي من قاعدة البيانات.
- تم إزالة fallback labels وعناوين PDF من خدمات `AdmissionPdfService`, `EnrollmentCertificatePdfService`, `PrikazPdfService`, و `StudyContractPdfService`.
- عند نقص إعداد PDF أو workflow يظهر marker تقني مثل `[missing:pdf.admission.labels.0]` بدلاً من إخفاء المشكلة بنص ثابت.
- تم تحويل أسماء خطوات workflow والحالات وإشعار إصدار القبول إلى مفاتيح في جدول `settings`.
- تم تعديل `WorkflowConfigurationSeeder` ليستخدم `Setting::firstOrCreate` لإعدادات workflow/pdf حتى لا يكتب فوق تعديلات apanel عند إعادة تشغيله.
- تم إدخال مفاتيح settings الجديدة في قاعدة البيانات باستخدام `firstOrCreate` فقط، بدون حذف أو overwrite.
- تم إزالة fallback `localhost` من رابط إشعار الإعلان.

## ربط Laravel App Backend بالبيانات

- جدول اللغات: `locales`.
- جداول الترجمات العامة: `translation_keys`, `translation_values`.
- جداول ترجمات المحتوى: جداول `*_translations` الموجودة لكل نموذج مثل departments, programs, pages, videos, footer, settings.
- جدول إعدادات PDF وworkflow: `settings`.
- API المستخدم: `/api/v1/locales`, `/api/v1/translations`, `/api/v1/settings/public`, `/api/v1/applications/initial/metadata`, ومسارات `/api/v1/student/*` و `/api/v1/apanel/applications-workflow/*`.
- إدارة `/apanel/`: اللغات والترجمات من صفحات apanel الخاصة بها، وإعدادات CMS العامة من صفحات `/apanel/cms/*`، وworkflow من `/apanel/applications-workflow`.

## تحقق Laravel App Backend

- `php-local.bat -l` نجح للملفات PHP المعدلة.
- تم إدخال مفاتيح settings الجديدة بـ `Setting::firstOrCreate` فقط.
- إعادة المسح لم تجد fallback ثابت `locale = en` أو قائمة لغات ثابتة أو fallback labels داخل خدمات PDF المعدلة.
- `php-local.bat artisan optimize:clear` نجح.
- `php-local.bat artisan route:list --path=api/v1 --except-vendor` نجح وأظهر 170 route.
- `php-local.bat artisan test` نجح: 5 tests passed, 8 assertions.
- `npm.cmd run lint` نجح.
- `npm.cmd run build` نجح.

## المتبقي في Laravel App Backend

- `OrganizeStorageCommand.php` يحتوي خريطة مسارات تخزين تقنية لتنظيم الملفات داخل Laravel storage؛ لم يتم تحويلها إلى قاعدة البيانات لأنها ليست محتوى ظاهر للمستخدم.
- بعض رسائل API التقنية ورسائل validation ما زالت داخل الكود لأنها منطق تحقق وتشخيص، وليست محتوى CMS قابل للتحرير.

---

## مراجعة Laravel Resources Config Bootstrap

تاريخ المراجعة: 2026-07-28

## ملفات Laravel Resources Config Bootstrap التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\resources`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\resources\views`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\resources\js\app.js`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\resources\css\app.css`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\node_modules`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\config`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\bootstrap`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\bootstrap\cache`

## ما تم العثور عليه في Laravel Resources Config Bootstrap

- `resources/views` غير موجود به ملفات views فعلية في هذه المجموعة.
- `resources/js/app.js` و `resources/css/app.css` يحتويان bootstrap/Tailwind setup فقط، ولا يوجد بهما محتوى موقع أو صور أو روابط API.
- `node_modules` يحتوي حزم طرف ثالث ناتجة عن npm، وليس مصدر محتوى للمشروع ولا يتم ربطه بـ `/apanel/`.
- `bootstrap/app.php` و `bootstrap/providers.php` يحتويان إعداد Laravel وميدلوير وأخطاء API تقنية فقط.
- `bootstrap/cache/packages.php` و `bootstrap/cache/services.php` ملفات manifest/cache مولدة من Composer/Laravel، وليست مصدر محتوى CMS.
- ملفات `config` كانت تحتوي defaults محلية مثل `http://localhost`, `127.0.0.1`, و `localhost` في إعدادات URL/CORS/Sanctum/storage/database/mail/cache/queue.

## تغييرات Laravel Resources Config Bootstrap

- تمت إزالة fallback المحلي من `config/app.php` لقيمة `APP_URL`.
- تمت إزالة اسم التطبيق الثابت `BSTU Platform` من `config/app.php` حتى يأتي الاسم من `.env`.
- تمت إزالة origins المحلية الثابتة من `config/cors.php`.
- تم جعل رابط public disk في `config/filesystems.php` يعتمد على `APP_URL` أو `/storage` بدون `localhost`.
- تمت إزالة قائمة Sanctum المحلية الثابتة من `config/sanctum.php`.
- تمت إزالة defaults المحلية من `DB_HOST`, `REDIS_HOST`, `MEMCACHED_HOST`, `MAIL_HOST`, و `BEANSTALKD_QUEUE_HOST`.
- تمت إزالة مثال SQS URL الثابت من `config/queue.php`.

## ربط Laravel Resources Config Bootstrap بالبيانات

- قاعدة البيانات: لا يوجد محتوى CMS جديد في هذه المجموعة.
- Laravel API: لا توجد endpoints جديدة؛ هذه ملفات إعداد وتشغيل.
- `/apanel/`: لا ينطبق على ملفات config/bootstrap/resources لأنها ليست بيانات يديرها admin.
- نظام اللغات: `config/app.php` بقي يحتوي `APP_LOCALE` و `APP_FALLBACK_LOCALE` كـ Laravel technical fallback فقط، أما نظام اللغات الفعلي في المشروع فيأتي من جدول `locales` عبر `/api/v1/locales`.

## تحقق Laravel Resources Config Bootstrap

- تم فحص ملفات PHP المعدلة بـ `php-local.bat -l` ونجحت كلها.
- إعادة المسح لم تجد `http://localhost`, `localhost:`, `127.0.0.1`, `VITE_API_BASE_URL`, `/admin`, `mock`, `demo`, أو `BSTU Platform` داخل المجموعة.
- `php-local.bat artisan optimize:clear` نجح.
- `php-local.bat artisan route:list --path=api/v1 --except-vendor` نجح وأظهر 170 route.
- `php-local.bat artisan config:show app.name` أكد أن الاسم يأتي من البيئة: `Platform BSTU`.
- `php-local.bat artisan test` نجح: 5 tests passed, 8 assertions.
- `npm.cmd run lint` نجح.
- `npm.cmd run build` نجح.

## المتبقي في Laravel Resources Config Bootstrap

- بقيت defaults تقنية مثل أسماء drivers والجداول وqueues وcache stores لأنها إعدادات تشغيل وليست محتوى قابل للتحرير من `/apanel/`.
- بقي `resources/css/app.css` يحتوي `@source '../../storage/framework/views/*.php'` كمسار Tailwind تقني لفحص Blade cache، وليس رابط تخزين عام أو محتوى صورة.

---

## مراجعة React Entry And Build Files

تاريخ المراجعة: 2026-07-28

## ملفات React Entry And Build التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\main.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\App.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\App.css`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\index.css`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\utils`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\utils\cmsContent.js`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\index.html`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\vite.config.js`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\tailwind.config.js`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\eslint.config.js`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\.env`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\.env.example`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\package.json`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\package-lock.json`

## ما تم تأكيده وتعديله في React Entry And Build

- `main.jsx` لم يعد يلف التطبيق بـ `LanguageProvider` إضافي؛ مصدر اللغة الحالي من `LocaleProvider` داخل `App.jsx`.
- `App.jsx` لم يعد يحتوي رسائل loading ثابتة، ويستخدم `LoadingState` الذي يأخذ النص من نظام الترجمات.
- `index.html` لم يعد يحتوي title/meta/keywords/favicons ثابتة؛ هذه القيم يتم ضبطها من `settings` عبر `LocaleContext.jsx`.
- `.env` و `.env.example` لم تعد تحتوي `VITE_DEFAULT_LOCALE` أو `VITE_SUPPORTED_LOCALES`; اللغات تأتي من جدول `locales`.
- `cmsContent.js` لم يعد يحتوي fallback image ثابت أو default locale ثابت.
- تم حذف `App.css` لأنه ملف قالب غير مستخدم ولا يدخل في التشغيل.

## نتيجة React Entry And Build

- لا توجد بيانات محتوى static داخل ملفات entry/build/utils التي تمت مراجعتها.
- ملفات config مثل `vite.config.js`, `tailwind.config.js`, `eslint.config.js`, `package.json`, و `package-lock.json` تحتوي إعدادات تشغيل واعتمادات فقط، وليست محتوى قابل للإدارة من apanel.
- القيم القابلة للإدارة مثل title/meta/favicon/logo/languages/translations تأتي من قاعدة البيانات عبر API.

## تحقق React Entry And Build

- `npm.cmd run lint` نجح.
- `npm.cmd run build` نجح.
- لم يعد يوجد داخل الملفات المطلوبة `BSTU International`, `Bukhara State...`, `cms/branding`, `Checking session`, `Verifying admin`, `Loading page contents`, `FALLBACK_IMAGE`, `VITE_DEFAULT_LOCALE`, أو `VITE_SUPPORTED_LOCALES`.

---

## مراجعة React Sections

تاريخ المراجعة: 2026-07-28

## ملفات React Sections التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\sections`
- `About.jsx`
- `AltFeatures.jsx`
- `Announcements.jsx`
- `Contact.jsx`
- `FAQ.jsx`
- `Hero.jsx`
- `GreenCampusSection.jsx`
- `Features.jsx`
- `Leadership.jsx`
- `News.jsx`
- `Programs.jsx`
- `RecentBlog.jsx`
- `RegistrarOffice.jsx`
- `Services.jsx`
- `Stats.jsx`
- `Testimonials.jsx`
- `Values.jsx`
- `VideoGallery.jsx`

## ما تم تأكيده وتعديله في React Sections

- `About.jsx` و `Features.jsx` يستخدمان `publicAssetUrl` من طبقة API بدل بناء روابط التخزين داخل section.
- `Hero.jsx` لم يعد يحتوي صور hero افتراضية أو رقم طلاب ثابت داخل React؛ الصور و `home_hero_student_count` تأتي من جدول `settings` عبر API.
- `Announcements.jsx`, `News.jsx`, و `VideoGallery.jsx` لم تعد تحتوي أسماء شهور أو وحدات زمن أوزبكية ثابتة؛ التنسيق يتم عبر `Intl`.
- `Programs.jsx` لم يعد يحتوي fallback labels إنجليزية داخل React؛ مفاتيح الفلاتر والبحث والحالات تأتي من جدول الترجمات.
- `RecentBlog.jsx` لم يعد يطبع رسالة خطأ ثابتة في console، ويتعامل مع الفشل بتفريغ الحالة.
- `Testimonials.jsx` لا يستخدم fallback content داخل `t()`، ويعرض العناصر فقط إذا رجعت كقائمة من نظام الترجمات.
- تمت إضافة مفاتيح الترجمة الناقصة في `StudentSystemTranslationSeeder`.
- تمت إضافة إعداد `home_hero_student_count` في `SettingSeeder` كقيمة public يمكن إدارتها من apanel.

## نتيجة React Sections

- لا توجد بيانات محتوى static مهمة داخل `apps/web/src/sections`.
- النصوص والـ labels والصور والأرقام القابلة للإدارة تأتي من قاعدة البيانات عبر API.
- المتبقي داخل sections هو منطق عرض، icons، class names، ألوان تقنية، routes داخلية، وتنسيق واجهة.

## تحقق React Sections

- `npm.cmd run lint` نجح.
- `npm.cmd run build` نجح.
- `php-local.bat artisan db:seed --class=SettingSeeder` نجح بدون حذف بيانات.
- `php-local.bat artisan db:seed --class=StudentSystemTranslationSeeder` نجح بدون حذف بيانات.
- `php-local.bat artisan optimize:clear` نجح.

---

## مراجعة React Pages

تاريخ المراجعة: 2026-07-28

## ملفات React Pages التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\pages`
- `VideoBDTU.jsx`
- `ServicesPage.jsx`
- `RegisterPage.jsx`
- `ProgramDetails.jsx`
- `ProgramsPage.jsx`
- `ProfileDetails.jsx`
- `NewsPage.jsx`
- `NewsDetails.jsx`
- `LoginPage.jsx`
- `Home.jsx`
- `GreenCampusPage.jsx`
- `GreenCampusDetails.jsx`
- `ForgotPassword.jsx`
- `FacultyDetails.jsx`
- `FacultiesPage.jsx`
- `DepartmentsPage.jsx`
- `DepartmentPage.jsx`
- `ContactPage.jsx`
- `CmsListPage.jsx`
- `CmsDetailPage.jsx`
- `CenterDetails.jsx`
- `BlogDetails.jsx`
- `Blog.jsx`
- `ApplyPage.jsx`
- `AnnouncementsPage.jsx`
- `AnnouncementDetails.jsx`
- `AdministrationDetails.jsx`
- `AboutPage.jsx`

## ما تم تأكيده وتعديله في React Pages

- `ApplyPage.jsx` لم يعد يحتوي قاموس ترجمة محلي؛ كل النصوص أصبحت من مفاتيح `initialApplication.*` في جدول الترجمات.
- صفحات الأخبار والإعلانات وGreen Campus والفيديو لم تعد تحتوي أسماء شهور أو وحدات زمن محلية ثابتة، وتستخدم `Intl` حسب اللغة.
- صفحات CMS وfaculty/department/program/profile/administration/center لم تعد تحتوي fallback labels إنجليزية داخل `t()`.
- `AboutPage.jsx` يستخدم `publicAssetUrl` بدل بناء رابط التخزين من `VITE_API_BASE_URL` داخل الصفحة.
- تمت إزالة `console.error` من صفحات blog/video/center.
- تمت إضافة مفاتيح الترجمة الناقصة في `StudentSystemTranslationSeeder`.

## نتيجة React Pages

- لا توجد بيانات محتوى static ظاهرة داخل ملفات `apps/web/src/pages` التي تمت مراجعتها.
- محتوى الصفحات، العناوين، labels، رسائل النماذج، الفئات، الأخبار، الفيديوهات، البرامج، الكليات، الأقسام، والمراكز تأتي من قاعدة البيانات عبر API أو من جدول الترجمات.
- المتبقي داخل هذه الصفحات هو منطق عرض، أسماء متغيرات، routes داخلية، icons، class names، وتعليقات تقنية غير معروضة للمستخدم.

## تحقق React Pages

- `php-local.bat -l database/seeders/StudentSystemTranslationSeeder.php` نجح.
- `php-local.bat artisan db:seed --class=StudentSystemTranslationSeeder` نجح بدون حذف بيانات.
- `php-local.bat artisan optimize:clear` نجح.
- `npm.cmd run lint` نجح.
- `npm.cmd run build` نجح.
- لا توجد استدعاءات `t("key", "static fallback")` داخل `apps/web/src/pages`.

---

## مراجعة Tooling And Workflow Config

تاريخ المراجعة: 2026-07-28

## ملفات Tooling And Workflow Config التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\.agents`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\.vscode`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\.vscode\settings.json`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\.github`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\.github\workflows`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\.github\workflows\issues.yml`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\.github\workflows\pull-requests.yml`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\.github\workflows\tests.yml`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\.github\workflows\update-changelog.yml`

## ما تم العثور عليه في Tooling And Workflow Config

- `.agents` موجود لكنه فارغ.
- `.vscode/settings.json` يحتوي إعدادات محرر وبحث واستثناءات ملفات تقنية فقط.
- ملفات `.github/workflows` تحتوي GitHub Actions خاصة بالـ issues, pull requests, tests, changelog.
- لا توجد نصوص موقع، صور، روابط CMS، أرقام محتوى، أو بيانات قابلة لإدارة `/apanel/` داخل هذه المجموعة.
- لا توجد حاجة لإنشاء جدول أو Model أو API أو صفحة `/apanel/` لهذه الملفات لأنها إعدادات تطوير وتشغيل وليست محتوى عام.

## تغييرات Tooling And Workflow Config

- تمت إزالة `dart.flutterSdkPath` و `dart.sdkPath` من `.vscode/settings.json` لأنها مسارات Windows محلية خاصة بجهاز واحد وليست إعدادات مشروع قابلة للمشاركة.
- بقيت استثناءات `apps/api/storage/framework/views` لأنها إعدادات تقنية للمحرر والبحث، وليست روابط تخزين أو محتوى.

## ربط Tooling And Workflow Config بالبيانات

- قاعدة البيانات: لا ينطبق؛ لا يوجد محتوى قابل للإدارة.
- Laravel API: لا ينطبق؛ الملفات لا تعرض محتوى للمستخدم.
- `/apanel/`: لا ينطبق؛ هذه إعدادات أدوات وCI وليست عناصر CMS.
- نظام اللغات: لا ينطبق؛ لا توجد نصوص واجهة أو ترجمات داخل هذه الملفات.

## تحقق Tooling And Workflow Config

- تم التحقق أن `.vscode/settings.json` صالح JSON.
- تم التحقق أن `.agents` فارغ.
- تم البحث داخل `.vscode` و `apps/api/.github` عن روابط API أو storage أو محتوى CMS؛ لم يظهر إلا استثناء تقني لـ `storage/framework/views`.
- `npm.cmd run lint` نجح.
- `npm.cmd run build` نجح.
- `php-local.bat artisan test` نجح: 5 tests passed.
- `php-local.bat artisan route:list --path=api/v1 --except-vendor` نجح وأظهر 170 route.

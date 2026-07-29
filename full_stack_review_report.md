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

## مراجعة Pages Sections Dynamic Cleanup 2026-07-28

تاريخ المراجعة: 2026-07-28

## مسارات Pages Sections Dynamic Cleanup

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\pages`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\sections`
- الملفات المباشرة المطلوبة لدعم الاتصال الديناميكي:
  - `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\app\Http\Controllers\Api\InitialApplicationController.php`
  - `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\seeders\WorkflowConfigurationSeeder.php`
  - `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\seeders\StudentSystemTranslationSeeder.php`

## محتوى Pages Sections Dynamic Cleanup

- وجدت بقايا منطق لغة ثابت مثل خرائط `en/uz/ru/ar` داخل تنسيق التاريخ والأرقام في صفحات الإعلانات والفيديو وGreen Campus.
- وجدت خيارات ثابتة في `ApplyPage.jsx` مثل messenger وstudent type وdegree labels كانت تُحوّل داخل React.
- وجدت استخداماً قديماً لمسار `/assets/img` داخل `DepartmentPage.jsx`.
- لم أجد mock arrays أو demo content مستخدمة كمصدر محتوى داخل هذه المجموعة بعد التعديل.

## تغييرات Pages Sections Dynamic Cleanup

- أزلت خرائط اللغة الثابتة من `Announcements.jsx`, `AnnouncementDetails.jsx`, `AnnouncementsPage.jsx`, `VideoGallery.jsx`, `VideoBDTU.jsx`, `GreenCampusPage.jsx`, و`GreenCampusDetails.jsx`، وأصبح التنسيق يستخدم كود اللغة القادم من API عبر `useLanguage()`.
- جعلت اتجاه RTL يعتمد على `isRtl` القادم من نظام اللغة بدل مقارنة `language === "ar"` داخل صفحات وsections المجموعة.
- نقلت خيارات messenger في نموذج التقديم من React إلى إعداد `application.messengers` في قاعدة البيانات.
- جعلت `InitialApplicationController` يرسل `messengers` ضمن metadata ويتحقق منها من إعدادات قاعدة البيانات.
- أضفت مفاتيح ترجمة خيارات نموذج التقديم مثل `initialApplication.options.gender.*`, `initialApplication.options.messenger.*`, `initialApplication.options.degree_level.*`, `initialApplication.options.education_type.*`, و`initialApplication.yearsLabel`.
- أزلت fallback النصي المحلي من خيارات نموذج التقديم؛ قيم labels الآن تأتي من جدول الترجمات، بينما قيم intakes تبقى كما تأتي من إعدادات قاعدة البيانات لأنها محتوى موسمي قابل للإدارة.
- جعلت `StudentSystemTranslationSeeder` يستخدم `firstOrCreate` للترجمات حتى ينشئ المفاتيح والقيم الناقصة فقط ولا يكتب فوق تعديلات `/apanel/` عند تشغيله مرة أخرى.

## ربط Pages Sections Dynamic Cleanup

- قاعدة البيانات:
  - `settings` لإعدادات `application.messengers` وبقية metadata الخاصة بالتقديم.
  - `translation_keys` و`translation_values` لترجمة labels وخيارات النموذج.
  - جداول CMS الموجودة للمحتوى العام: `pages`, `page_blocks`, `about_pages`, `announcements`, `blogs`, `news`, `videos`, `green_campus_articles`, `green_campus_settings`, `services`, `programs`, `faculties`, `departments`, `staff_profiles`, `administration_profiles`, وملحقات الترجمات الخاصة بها.
- Laravel API:
  - `GET /api/v1/applications/initial/metadata`
  - `POST /api/v1/applications/initial`
  - `GET /api/v1/locales`
  - `GET /api/v1/translations`
  - endpoints المحتوى العامة مثل `/api/v1/home`, `/api/v1/pages`, `/api/v1/announcements`, `/api/v1/news`, `/api/v1/blog`, `/api/v1/videos`, `/api/v1/green-campus/*`, `/api/v1/programs`, `/api/v1/faculties`, و`/api/v1/departments`.
- `/apanel/`:
  - إدارة الترجمات من صفحة الترجمات.
  - إدارة اللغات من صفحة locales.
  - إدارة إعدادات CMS من صفحات `/apanel/cms/*`.
  - إدارة محتوى الأخبار والإعلانات والفيديو والمدونة والخدمات والبرامج والكليات والأقسام عبر موارد `/apanel`.
  - إعدادات workflow/application قابلة للتوسعة عبر جدول `settings` ولا تتطلب فتح React code.

## تحقق Pages Sections Dynamic Cleanup

- إعادة فحص الملفات أكدت عدم وجود `t("key", "static fallback")` داخل `apps/web/src/pages` و`apps/web/src/sections`.
- إعادة فحص الملفات أكدت عدم وجود `VITE_API_BASE_URL`, `localhost`, `127.0.0.1`, `/assets/img`, أو `public\assets\img` داخل المجموعة.
- `npm.cmd run lint` نجح.
- `npm.cmd run build` نجح.
- `php-local.bat -l app\Http\Controllers\Api\InitialApplicationController.php` نجح.
- `php-local.bat -l database\seeders\WorkflowConfigurationSeeder.php` نجح.
- `php-local.bat -l database\seeders\StudentSystemTranslationSeeder.php` نجح.
- `php-local.bat artisan db:seed --class=WorkflowConfigurationSeeder` نجح بدون حذف بيانات.
- `php-local.bat artisan db:seed --class=StudentSystemTranslationSeeder` نجح بدون حذف بيانات.
- `php-local.bat artisan route:list --path=api/v1 --except-vendor` نجح وأظهر 170 route.
- `php-local.bat artisan test` نجح: 4 tests passed.
- `php-local.bat artisan optimize:clear` نجح.

---

## مراجعة Laravel Database CMS Seed Safety 2026-07-29

تاريخ المراجعة: 2026-07-29

## ملفات Laravel Database CMS Seed Safety 2026-07-29 التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\data`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\migrations`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\seeders`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\factories\UserFactory.php`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\.gitignore`

## ما تم العثور عليه في Laravel Database CMS Seed Safety 2026-07-29

- كانت seeders كثيرة تستخدم `updateOrCreate` بطريقة قد تعيد كتابة محتوى عدله الأدمن من `/apanel/`.
- كان `MenuSeeder.php` يحذف عناصر قائمة الهيدر قبل إعادة إنشائها، وهذا كان سيمس الترتيب والنشر والتعديلات المدارة من `/apanel/header-navbar`.
- كانت بعض seeders الأكاديمية تعطل سجلات موجودة عبر `is_active = false` أثناء إعادة البذر.
- كان `TranslationValueSeeder.php` يستخدم قائمة لغات ثابتة ويملأ الترجمات الناقصة بالإنجليزية أو بالمفتاح نفسه.
- كان `academic_department_details.json` يحتوي تعليمات قديمة، روابط `localhost`, قسم `mockData`, ومفاتيح صور legacy من نوع `fallbackImage`/`fallback`.

## تغييرات Laravel Database CMS Seed Safety 2026-07-29

- تم تحويل seeders التي كانت تعيد كتابة البيانات إلى إنشاء السجلات المفقودة فقط باستخدام `firstOrCreate`.
- تمت إضافة `Database\Seeders\Concerns\ResolvesSeedLocales` لاستخدام اللغات النشطة من جدول `locales` داخل seeders بدلاً من تثبيت قائمة اللغات في منطق البذر.
- تم تعديل `TranslationValueSeeder.php` ليقرأ اللغات من قاعدة البيانات، ويتخطى الترجمة الناقصة بدل تخزين fallback إنجليزي أو اسم المفتاح.
- تم تعديل `MenuSeeder.php` ليحافظ على قائمة الهيدر الموجودة إذا كانت تحتوي عناصر، ولا يحذف أو يعيد ترتيب ما أداره الأدمن.
- تم إيقاف عمليات تعطيل المحتوى الجماعية داخل seeders الأكاديمية والتقنية، مع إبقاء إنشاء المحتوى المفقود.
- تم تنظيف `academic_department_details.json` من روابط التطوير، تعليمات التحكم القديمة، `mockData`, ومفاتيح fallback الخاصة بالصور.
- تم تقليل seed fallbacks العامة في seeders الصفحات، الكليات، الأقسام، البرامج، المقررات، والكادر بحيث لا تنشئ ترجمة للغة لا تملك قيمة مصدر.

## ربط Laravel Database CMS Seed Safety 2026-07-29 بالبيانات

- اللغات الديناميكية: جدول `locales`، Model `Locale`، API `GET /api/v1/locales`، وإدارة `/apanel/locales`.
- الترجمات: جداول `translation_keys` و `translation_values`، API `GET /api/v1/translations`، وإدارة `/apanel/translations`.
- القوائم والهيدر: جداول `menus`, `menu_items`, `menu_item_translations`، API `GET /api/v1/menus` و `GET /api/v1/menus/{location}`، وإدارة `/apanel/header-navbar`.
- الكليات والأقسام والبرامج والمقررات: جداول `faculties`, `departments`, `programs`, `courses` وجداول translations التابعة لها، API public تحت `/api/v1/faculties`, `/api/v1/departments`, `/api/v1/programs`, `/api/v1/courses`، وإدارة `/apanel/faculties`, `/apanel/departments`, `/apanel/programs`.
- الكادر والإدارة: جداول `staff_profiles`, `staff_profile_translations`, و `administration_*`، API `GET /api/v1/staff`, `GET /api/v1/administration`، وإدارة `/apanel/administration` وموارد staff في `/apanel`.
- الإعدادات والوسائط: جداول `settings` و `media`, API `GET /api/v1/settings`, `GET /api/v1/media/{id}`، وإدارة `/apanel/media` وصفحات إعدادات CMS ذات الصلة.
- سير الطالب والطلبات: جداول student/application workflow، API `/api/v1/student/*`, `/api/v1/applications/*`, `/api/v1/apanel/applications-workflow/*`، وإدارة `/apanel/applications-workflow`.

## المتبقي بعد Laravel Database CMS Seed Safety 2026-07-29

- توجد migration تاريخية `2026_07_16_000005_remove_legacy_footer_translation_keys.php` تحتوي حذفًا لمفاتيح footer legacy. لم يتم تعديلها لأنها migration قديمة وقد تكون منفذة، وتغيير migrations التاريخية قد يسبب عدم تطابق بين البيئات. يلزم قرار منفصل إن كان يجب استبدال أثرها بمسار migration تعويضي.
- بقيت بيانات seed فعلية داخل `database/data` و seeders، وهذا مقبول فقط كبيانات أولية قابلة للإدارة من `/apanel/` وليست runtime fallback داخل React.

## تحقق Laravel Database CMS Seed Safety 2026-07-29

- تم فحص `localhost`, `127.0.0.1`, `/admin`, `mockData`, `fallbackImage`, و image fallback داخل `apps\api\database`: لم تظهر نتائج ذات صلة بعد التنظيف.
- تم فحص قوائم اللغات الثابتة `['en', 'uz', 'ru', 'ar']`: لم تظهر داخل seeders/data/migrations بعد إضافة helper اللغات.
- تم فحص `updateOrCreate`, `delete`, `truncate`, وتعطيل `is_active` داخل seeders: لم يبق إلا migration footer legacy المذكورة أعلاه.
- `php-local.bat -l` نجح لكل ملفات PHP المعدلة داخل `apps\api\database`.
- `npm.cmd run lint` نجح مع تحذيرين قديمين في ملفات student خارج هذه الجولة، بدون أخطاء.
- `npm.cmd run build` نجح.
- `php-local.bat artisan route:list --path=api/v1` نجح وأظهر 170 route.
- `php-local.bat artisan test` نجح: 4 tests passed, 7 assertions.
- `php-local.bat artisan optimize:clear` نجح.

## متبقي Pages Sections Dynamic Cleanup

- لا يوجد محتوى ظاهر قابل للإدارة بقي static داخل هذه المجموعة حسب الفحص الحالي.
- بقيت داخل الملفات أسماء دوال، routes داخلية، icons، CSS classes، مفاتيح ترجمة، وتنسيق أرقام/تواريخ تقني فقط.

---

## مراجعة Features Apanel Student 2026-07-28

تاريخ المراجعة: 2026-07-28

## مسارات Features Apanel Student

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\student`
- الملف المباشر المطلوب للترجمات:
  - `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\seeders\StudentSystemTranslationSeeder.php`

## محتوى Features Apanel Student الذي تم العثور عليه

- وجدت fallbacks نصية داخل student بصيغة `t("key", "Static text")`.
- وجدت بناء روابط storage/API داخل صفحات apanel وstudent بدل استخدام helper مركزي.
- وجدت مصفوفة وثائق ثابتة داخل `StudentDocuments.jsx`.
- وجدت مقارنة `language === "ar"` داخل layouts.
- وجدت hardcoded locale arrays داخل بعض صفحات apanel CMS forms.
- وجدت رسائل وأزرار ثابتة متبقية داخل `ApanelApplicationsWorkflow.jsx` و`ApanelCrud.jsx`.

## تغييرات Features Apanel Student

- أزلت fallbacks النصية من ملفات student التي كانت تستخدم `t("key", "fallback")`.
- جعلت `StudentDocuments.jsx` يأخذ checklist الوثائق من `GET /api/v1/student/documents/checklist` بدل مصفوفة ثابتة داخل React.
- جعلت روابط ملفات الطالب والعقود تستخدم `publicAssetUrl`.
- جعلت `MediaPicker.jsx` يخزن مسار media القادم من API ويستخدم `publicAssetUrl` للمعاينة بدل إنشاء `/storage/...` داخل المكون.
- استبدلت بناء روابط storage اليدوي في صفحات apanel التالية بـ `publicAssetUrl`:
  - `ApanelAboutPage.jsx`
  - `ApanelAdministration.jsx`
  - `ApanelAnnouncements.jsx`
  - `ApanelApplicationDetail.jsx`
  - `ApanelBlog.jsx`
  - `ApanelCenters.jsx`
  - `ApanelGreenCampus.jsx`
  - `ApanelMedia.jsx`
  - `ApanelNewsEvents.jsx`
  - `ApanelVideoBdtu.jsx`
- جعلت `ApanelLayout.jsx` و`StudentLayout.jsx` يستخدمان `isRtl` من نظام اللغة الديناميكي.
- جعلت `TranslationTabs.jsx` يستخدم اللغات القادمة من `GET /api/v1/locales` عند عدم تمرير قائمة locales له.
- أضفت مفاتيح ترجمة جديدة لقائمة الطالب ورسائل الوثائق وtooltips الخاصة بالترجمة داخل `StudentSystemTranslationSeeder`.

## ربط Features Apanel Student بالبيانات

- قاعدة البيانات:
  - `locales` للغات واتجاه النص.
  - `translation_keys` و`translation_values` لنصوص الطالب والـ apanel tooltips التي أضيفت.
  - `document_requirements` و`application_documents` لقائمة وثائق الطالب.
  - جداول CMS الموجودة التي تديرها صفحات apanel مثل الأخبار، الإعلانات، الفيديو، المدونة، Green Campus، المراكز، الإدارة، الهيدر، الفوتر، والصفحات.
- Laravel API:
  - `GET /api/v1/locales`
  - `GET /api/v1/translations`
  - `GET /api/v1/student/documents/checklist`
  - `POST /api/v1/applications/{id}/documents/private`
  - `GET /api/v1/student/private-documents/{id}/download`
  - endpoints إدارة apanel مثل `/api/v1/apanel/{resource}` و`/api/v1/apanel/cms/*`.
- `/apanel/`:
  - اللغات من `/apanel/locales`.
  - الترجمات من `/apanel/translations`.
  - media من `/apanel/media`.
  - CMS من صفحات `/apanel/cms/*` والموارد العامة تحت `/apanel`.

## تحقق Features Apanel Student

- `npm.cmd run lint` نجح.
- `npm.cmd run build` نجح.
- `php-local.bat -l database\seeders\StudentSystemTranslationSeeder.php` نجح.
- `php-local.bat artisan db:seed --class=StudentSystemTranslationSeeder` نجح بدون حذف بيانات.
- `php-local.bat artisan route:list --path=api/v1 --except-vendor` نجح وأظهر 170 route.
- `php-local.bat artisan test` نجح: 4 tests passed.
- `php-local.bat artisan optimize:clear` نجح.
- تم حذف ملفات PHPUnit المؤقتة من `storage/temp` و`.phpunit.result.cache`.

## متبقي Features Apanel Student

- لا تزال بعض صفحات apanel تحتوي قوائم locales ثابتة داخل state initialization، مثل `ApanelAboutPage.jsx`, `ApanelAdministration.jsx`, `ApanelAnnouncements.jsx`, `ApanelBlog.jsx`, `ApanelCenters.jsx`, `ApanelContactPage.jsx`, `ApanelFooterWeb.jsx`, `ApanelGreenCampus.jsx`, `ApanelHeaderNavbar.jsx`, `ApanelInteractiveServices.jsx`, `ApanelNewsEvents.jsx`, و`ApanelVideoBdtu.jsx`. لم أحذفها في هذه الجولة لأنها مرتبطة ببنية forms والترجمات وتحتاج تحويلًا أوسع حتى لا نفقد حقول التحرير.
- لا تزال `ApanelApplicationsWorkflow.jsx` و`ApanelCrud.jsx` تحتوي رسائل أزرار/توست/prompt ثابتة. تحتاج نقلها لاحقًا إلى مفاتيح ترجمة أو إعدادات workflow مناسبة.
- لا أدعي أن كل `features/apanel` أصبح ديناميكيًا بالكامل؛ تم إصلاح الروابط، RTL، بعض student labels، وdocument checklist، وبقيت عناصر apanel المذكورة أعلاه.

---

## مراجعة Web Public Root Environment

تاريخ المراجعة: 2026-07-28

## ملفات Web Public Root Environment التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\public`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\public\robots.txt`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\node_modules`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\vite.config.js`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\tailwind.config.js`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\package.json`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\package-lock.json`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\index.html`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\eslint.config.js`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\.env`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\.env.example`

## ما تم العثور عليه في Web Public Root Environment

- `public` يحتوي `robots.txt` فقط، وهو ملف SEO/crawling تقني وليس محتوى CMS.
- `node_modules` يحتوي مكتبات طرف ثالث وملفات dependency cache، ولا يتم نقله إلى قاعدة البيانات أو تعديله يدوياً.
- `vite.config.js`, `tailwind.config.js`, `eslint.config.js`, `package.json`, و `package-lock.json` تحتوي إعدادات بناء واعتمادات فقط.
- `index.html` يحتوي bootstrap HTML فقط. العنوان وmeta/favicon يتم تحديثها وقت التشغيل من `settings` عبر `LocaleContext.jsx`.
- `.env` ملف بيئة محلي وتمت مراجعته بدون عرض قيمه أو تعديلها.
- `.env.example` كان يحتوي قيمة API محلية `127.0.0.1`.

## تغييرات Web Public Root Environment

- تم تعديل `apps/web/.env.example` وإزالة رابط API المحلي، وأصبح `VITE_API_BASE_URL=` فارغاً ليتم ضبطه من بيئة التشغيل.
- لم يتم إنشاء جداول أو Models أو صفحات `/apanel/` جديدة، لأن الملفات في هذه المجموعة ليست محتوى قابل للإدارة.
- تم تنظيف ملفات runtime المؤقتة التي أنشأتها فحوصات Laravel داخل `apps/api/storage/temp` و `.phpunit.result.cache`.

## ربط Web Public Root Environment بالبيانات

- قاعدة البيانات: لا توجد بيانات CMS جديدة في هذه المجموعة.
- Laravel API: الواجهة تستخدم طبقة API المركزية، وقيمة `VITE_API_BASE_URL` تأتي من بيئة التشغيل وليست hardcoded داخل القالب.
- `/apanel/`: محتوى branding وsite title وmeta وfavicon يدار من إعدادات CMS الموجودة في apanel ويعرض عبر `/api/v1/settings/public`.
- نظام اللغات: اللغات الفعلية تأتي من جدول `locales` عبر `/api/v1/locales`، و`index.html` يعمل كغلاف أولي فقط قبل تحميل React.

## تحقق Web Public Root Environment

- إعادة المسح داخل الملفات المطلوبة لم تجد `localhost`, `127.0.0.1`, `VITE_API_BASE_URL=http`, `/storage`, `assets/img`, `cms/branding`, أو hardcoded `t("key", "fallback")`.
- تم التأكد أن `apps/web/node_modules/.vite-temp` فارغ.
- `npm.cmd run lint` نجح.
- `npm.cmd run build` نجح.
- `php-local.bat artisan route:list --path=api/v1 --except-vendor` نجح وأظهر 170 route.
- `php-local.bat artisan test` نجح: 4 tests passed, 7 assertions.
- `php-local.bat artisan optimize:clear` نجح.

---

## Documentation Cleanup Pass 2026-07-29

تاريخ التنظيف: 2026-07-29

## Documentation Cleanup Pass 2026-07-29 paths reviewed

- `docs`
- `database-docs`
- `storage-docs`
- `README.md`
- `AGENTS.md`
- `.gitignore`
- `international.bstu.uz.code-workspace`
- `after.md`
- `walkthrough.md`
- `engineering_faculty_drafts.md`
- `legacy_academic_drafts.md`
- `project_information_export.md`
- `full_stack_review_report.md`

## Documentation Cleanup Pass 2026-07-29 files kept

- `README.md`, `AGENTS.md`, `.gitignore`, `international.bstu.uz.code-workspace`, و `full_stack_review_report.md` لأنها ملفات دخول/ميتا مهمة.
- ملفات `docs`, `database-docs`, و `storage-docs` الحالية لأنها تمثل المصدر المنظم للتوثيق.

## Documentation Cleanup Pass 2026-07-29 files cleaned

- `AGENTS.md`: استبدال أمر `migrate:fresh --seed` العادي بأوامر `migrate` و `db:seed`، وتوضيح أن `migrate:fresh --seed` مخصص فقط لقواعد بيانات محلية قابلة للحذف.
- `docs/02_academic_and_cms_migration_specifications.md`: إضافة خلاصة مكان حفظ ملاحظات الهجرة بعد حذف المسودات.
- `docs/04_faculty_migration_notes.md`: إضافة ملاحظة أن ملفات المسودات الأكاديمية الجذرية حذفت لأن خلاصة الهجرة موجودة هنا وفي وثائق قاعدة البيانات.

## Documentation Cleanup Pass 2026-07-29 files deleted

- `after.md`: سجل عمل مؤقت باللغة العربية، ومحتواه عبارة عن توجيه مرحلي مكرر وموجود بشكل أفضل داخل تقرير المراجعة والوثائق المنظمة.
- `walkthrough.md`: سجل تاريخي لعملية resequencing محلية؛ يحتوي خطوات قد تكون مضللة أو خطرة إذا اعتبرت runbook حالي.
- `engineering_faculty_drafts.md`: مسودة محتوى كلية الهندسة، وخلاصتها المفيدة موجودة في `docs/04_faculty_migration_notes.md`.
- `legacy_academic_drafts.md`: ملف ضخم لمسودات كليات/أقسام/برامج وروابط محلية قديمة، واستبدلته الوثائق المنظمة في `docs/03_university_departments_and_centers.md`, `docs/04_faculty_migration_notes.md`, و `database-docs/database_migration_guide.md`.
- `project_information_export.md`: export ضخم ومكرر للمشروع، وليس مصدر توثيق عملي بعد وجود `database-docs` وملفات `docs` المنظمة.

## Documentation Cleanup Pass 2026-07-29 useful content moved

- خلاصة أن المسودات الأكاديمية القديمة لم تعد مصدرًا مباشرًا أضيفت إلى `docs/02_academic_and_cms_migration_specifications.md`.
- خلاصة حذف مسودات الكليات ومكان حفظ ملخصات الهجرة أضيفت إلى `docs/04_faculty_migration_notes.md`.
- لم تكن هناك حاجة لنقل export كامل؛ كان مكررًا وضخمًا ومناسبًا للحذف بعد وجود وثائق schema/migration الحالية.

## Documentation Cleanup Pass 2026-07-29 references updated

- لم تكن هناك روابط نشطة في `README.md` أو فهارس `docs` تشير إلى الملفات المحذوفة.
- تم فحص مراجع الملفات المحذوفة داخل المسارات المحددة؛ المتبقي فقط سجل تاريخي داخل `full_stack_review_report.md` وملاحظة حذف مقصودة داخل `docs/04_faculty_migration_notes.md`.

## Documentation Cleanup Pass 2026-07-29 manual decisions

- لا توجد ملفات متبقية تحتاج قرارًا يدويًا قبل الحذف ضمن هذه المجموعة.

## Documentation Cleanup Pass 2026-07-29 checks

- تم فحص مراجع الملفات المحذوفة قبل وبعد الحذف.
- تم فحص `/admin`, `/admin/`, `file:///`, روابط docs القديمة، و `VITE_SUPPORTED_LOCALES`: لا توجد نتائج داخل وثائق المشروع النشطة.
- تم فحص روابط Markdown النسبية في `README.md`, `docs`, `database-docs`, و `storage-docs`: الروابط المفحوصة تعمل.
- تم فحص عناوين `full_stack_review_report.md`: لا توجد headings مكررة من المستوى الثاني.
- تم تشغيل `git status` لمراجعة الملفات المعدلة والمحذوفة.
- لم يتم تشغيل ESLint أو React build أو Laravel/PHP checks لأن التغيير وثائقي فقط.

---

## مراجعة Documentation And Project Notes 2026-07-28

تاريخ المراجعة: 2026-07-28

## ملفات Documentation And Project Notes 2026-07-28 التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\database-docs`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\docs`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\storage-docs`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\.gitignore`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\after.md`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\AGENTS.md`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\engineering_faculty_drafts.md`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\international.bstu.uz.code-workspace`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\legacy_academic_drafts.md`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\project_information_export.md`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\walkthrough.md`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\README.md`

## توثيق Documentation And Project Notes 2026-07-28 الذي تم تنظيفه

- تم تحديث روابط `README.md` لتشير إلى أسماء ملفات الوثائق الفعلية الموجودة، بدلاً من أسماء قديمة مثل `docs/APANEL.md`, `docs/API.md`, و `database-docs/DATA_MIGRATION.md`.
- تم تصحيح روابط `docs/00_overview.md` من روابط Windows `file:///.../doc/...` إلى روابط Markdown نسبية داخل `docs`.
- تم تحديث وثائق اللغة في `README.md`, `docs/frontend_api_integration.md`, `docs/frontend_setup_guide.md`, `docs/web_frontend_development.md`, `docs/system_architecture.md`, `database-docs/database_schema.md`, و `database-docs/database_migration_guide.md` لتوضيح أن اللغات النشطة تأتي من جدول `locales` وتدار من `/apanel/locales`.
- تم تحديث وثائق الترجمة لتمنع hardcoded display fallbacks مثل `t("key", "Static text")` وتطلب أن تبقى الترجمات الناقصة قابلة للكشف.
- تم تحديث `storage-docs/laravel_storage_guide.md` ليطلب استخدام helper مركزي لحل روابط الملفات بدلاً من نسخ منطق `/storage/...` داخل الصفحات أو الخدمات.
- تم تصحيح `docs/student_system_guide.md` لتوثيق مستندات الطلاب كملفات workflow خاصة وليست public CMS media.
- تم تنظيم `walkthrough.md`, `after.md`, `engineering_faculty_drafts.md`, `legacy_academic_drafts.md`, و `project_information_export.md` بعلامات واضحة أنها سجلات/مسودات legacy وليست مصدر runtime أو مصدر seed مباشر.
- تم إصلاح ترقيم وسياق `docs/03_university_departments_and_centers.md` وتوضيح أن الملف ملخص وثائقي فقط.

## المحتوى القابل للعرض الذي وجد في Documentation And Project Notes 2026-07-28

- وجدت مسودات ومقتطفات عامة عن الكليات، الأقسام، البرامج، موظفي الكليات، المراكز، وأرقام/بيانات اتصال داخل `engineering_faculty_drafts.md`, `legacy_academic_drafts.md`, `project_information_export.md`, و `docs/03_university_departments_and_centers.md`.
- تمت مقارنة أمثلة رئيسية مثل Faculty of Engineering, Electrical and Power Engineering, Digital Educational Technologies Centre, Inclusive IT Center, Information Resource Center, و International Cooperation Department مع `apps/api/database/data` وseeders الأكاديمية، وظهر أنها ممثلة بالفعل في بيانات Laravel/MySQL الحالية مثل `translations.json`, `academic_department_details.json`, وseeders الأكاديمية.
- لذلك لم يتم إدخال نفس النصوص مرة ثانية إلى قاعدة البيانات حتى لا ننشئ محتوى مكررًا أو نظام seed موازٍ.

## ربط Documentation And Project Notes 2026-07-28 بالبيانات

- اللغات: `locales`.
- ترجمات واجهة النظام: `translation_keys`, `translation_values`.
- الكليات: `faculties`, `faculty_translations`.
- الأقسام: `departments`, `department_translations`.
- البرامج: `programs`, `program_translations`.
- المراكز والإدارة العامة: بيانات CMS/translation الموجودة في `apps/api/database/data/translations.json` وجداول CMS المرتبطة حسب الوحدة.
- ملفات الطلاب الخاصة: `application_documents` مع تخزين private workflow وتنزيل محمي.
- API اللغات والترجمات: `GET /api/v1/locales`, `GET /api/v1/translations`.
- API المحتوى العام والأكاديمي: endpoints العامة تحت `/api/v1`.
- إدارة المحتوى من `/apanel/`, خصوصًا `/apanel/locales`, `/apanel/translations`, `/apanel/faculties`, `/apanel/departments`, `/apanel/programs`, وصفحات CMS المتخصصة.

## المحتوى الذي بقي Documentation فقط في Documentation And Project Notes 2026-07-28

- أوامر الإعداد، مسارات التطوير المحلي، بنية المشروع، أدلة API، أدلة التخزين، وأدلة الاختبار بقيت في الوثائق لأنها معلومات تقنية وليست محتوى CMS.
- `legacy_academic_drafts.md`, `engineering_faculty_drafts.md`, و `project_information_export.md` بقيت كسجلات migration/legacy فقط لأنها تحتوي مسودات أو snapshots تحتاج تحقق يدوي قبل أي seed جديد، ومعظمها ممثل بالفعل في بيانات Laravel.
- روابط `localhost` و `127.0.0.1` بقيت فقط عندما كانت أمثلة تطوير محلية موضحة أو داخل ملف legacy معلّم بوضوح.

## المتبقي بعد Documentation And Project Notes 2026-07-28

- لا توجد قاعدة بيانات جديدة مطلوبة لهذه المجموعة.
- لا توجد ترجمات جديدة مضافة، لأن المحتوى العام الموجود في الوثائق كان إما ممثلًا مسبقًا في بيانات Laravel أو غير مؤكد كمسودة legacy.
- أي اختلاف بين `project_information_export.md` وبين قاعدة البيانات الحالية يجب التعامل معه كقرار محتوى لاحق قبل seed، وليس إدخالًا تلقائيًا.

## تحقق Documentation And Project Notes 2026-07-28

- تم فحص `/admin`, `/admin/`, روابط `file:///`, وروابط وثائق قديمة مثل `docs/APANEL.md`: لم تظهر نتائج بعد التنظيف داخل النطاق، باستثناء كلمات وصفية مثل admin panel دون route خاطئ.
- تم فحص `VITE_SUPPORTED_LOCALES` و hardcoded translation fallback patterns داخل الوثائق المحددة؛ تمت إزالة القائمة الثابتة من أدلة إعداد الواجهة.
- تم فحص `localhost`, `127.0.0.1`, و `/storage/`: المتبقي موثق كأمثلة تطوير محلية أو storage setup وليس منطق تنفيذ.
- تم فحص المحتوى الأكاديمي/المراكز مقابل `apps/api/database/data` وseeders، ولم تكن هناك حاجة إلى seed جديد.
- لم يتم تعديل ملفات React أو PHP implementation، لذلك لم يتم تشغيل ESLint أو Laravel tests لهذه الجولة.

---

## مراجعة React Apanel Shared Runtime 2026-07-28

تاريخ المراجعة: 2026-07-28

## ملفات React Apanel Shared Runtime 2026-07-28 التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\components`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\components\ConfirmDialog.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\components\DataTable.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\components\FormBuilder.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\components\MediaPicker.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\components\Pagination.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\components\SearchFilterBar.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\components\StatusBadge.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\components\TranslationTabs.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\layouts`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\layouts\ApanelLayout.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\utils`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\utils\locales.js`

## ما تم العثور عليه في React Apanel Shared Runtime 2026-07-28

- هذا النطاق كان يحتوي سابقاً على نصوص واجهة مشتركة وbranding وقائمة لغات ثابتة، وتمت معالجتها في مراجعة apanel السابقة.
- في إعادة الفحص الحالية لم تظهر fallbacks ترجمة أو قوائم لغات ثابتة أو روابط API/storage مكررة داخل هذا النطاق.
- النتائج الوحيدة المتبقية في الفحص النصي هي `console.error` و input `type="file"`، وهي تفاصيل تقنية وليست محتوى CMS.

## تغييرات React Apanel Shared Runtime 2026-07-28

- لم تكن هناك حاجة لتعديلات إضافية على ملفات هذا النطاق في هذه الجولة لأن التغييرات السابقة ما زالت سليمة.
- تم توثيق إعادة التحقق الحالية بشكل منفصل عن صفحات apanel الكبيرة.

## ربط React Apanel Shared Runtime 2026-07-28 بالبيانات

- قائمة اللغات في `ApanelLayout.jsx` تأتي من جدول `locales` عبر `GET /api/v1/locales`.
- نصوص الواجهة المشتركة تأتي من `translation_keys` و `translation_values` عبر `GET /api/v1/translations`.
- branding في `ApanelLayout.jsx` يأتي من جدول `settings` عبر `GET /api/v1/settings`.
- إدارة اللغات والترجمات من `/apanel/locales` و `/apanel/translations`.
- إدارة settings من resource `settings` داخل `/apanel` عبر AdminCrud.

## المتبقي بعد React Apanel Shared Runtime 2026-07-28

- لا يوجد داخل هذا النطاق الضيق محتوى قابل للإدارة باقٍ في الكود حسب الفحص الحالي.
- صفحات apanel الكبيرة خارج هذا النطاق ما زالت تحتوي بعض labels/placeholders/toasts ثابتة، وهي مسجلة في مراجعة `React Apanel Features 2026-07-28`.

## تحقق React Apanel Shared Runtime 2026-07-28

- تم فحص `t("key", "fallback")`: لم تظهر نتائج داخل النطاق.
- تم فحص قوائم اللغات الثابتة و `translations.en`: لم تظهر نتائج داخل النطاق.
- تم فحص `localhost`, `127.0.0.1`, `VITE_API_BASE_URL`, و `/storage/`: لم تظهر نتائج داخل النطاق.
- تم فحص branding/default labels مثل `BSTU`, `Control Panel`, `Apanel User`, `Delete`, `Cancel`, `Search...`, `No records found`, و `Browse`: لم تظهر نتائج داخل النطاق.
- تم التحقق أن كل مفاتيح `t("...")` داخل components/layouts/utils موجودة في seeder أو مولدة كـ status keys.
- `npm.cmd run lint -- --quiet`: نجح.
- `npm.cmd run build`: نجح.
- `php-local.bat -l database\seeders\StudentSystemTranslationSeeder.php`: نجح بدون أخطاء syntax.
- `php-local.bat artisan db:seed --class=StudentSystemTranslationSeeder`: نجح بدون حذف أو reset.
- `php-local.bat artisan route:list --path=api/v1 --except-vendor`: نجح وأظهر 170 route، ومنها `locales`, `translations`, `settings`, ومسارات `/apanel`.
- `php-local.bat artisan test`: نجح، 4 tests passed و 7 assertions.
- `php-local.bat artisan optimize:clear`: نجح.
- فحص عناوين `full_stack_review_report.md`: لا توجد عناوين Markdown مكررة.

---

## إكمال React Apanel Pages 2026-07-28

تاريخ المراجعة: 2026-07-28

## ملفات إكمال React Apanel Pages 2026-07-28 التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages`
- `ApanelAboutPage.jsx`
- `ApanelAdministration.jsx`
- `ApanelAnnouncements.jsx`
- `ApanelApplicationDetail.jsx`
- `ApanelApplicationsWorkflow.jsx`
- `ApanelBlog.jsx`
- `ApanelCenters.jsx`
- `ApanelContactManagement.jsx`
- `ApanelContactPage.jsx`
- `ApanelCrud.jsx`
- `ApanelDashboard.jsx`
- `ApanelFooterWeb.jsx`
- `ApanelGreenCampus.jsx`
- `ApanelHeaderNavbar.jsx`
- `ApanelInteractiveServices.jsx`
- `ApanelLocales.jsx`
- `ApanelLogin.jsx`
- `ApanelMedia.jsx`
- `ApanelNewsEvents.jsx`
- `ApanelNewsletterSubscriptions.jsx`
- `ApanelTranslations.jsx`
- `ApanelVideoBdtu.jsx`

## ما تم العثور عليه في إكمال React Apanel Pages 2026-07-28

- بعد المراجعة الجزئية السابقة، بقيت labels/placeholders/toasts ثابتة داخل صفحات apanel المتخصصة.
- المتبقي كان في props مثل `label`, `title`, `aria-label`, و `placeholder`، إضافة إلى بعض نصوص الجداول والأزرار ورسائل الخطأ.

## تغييرات إكمال React Apanel Pages 2026-07-28

- تم تحويل النصوص المتبقية في صفحات apanel إلى مفاتيح ترجمة عبر `useLanguage().t`.
- تم إنشاء `apps\api\database\data\apanel_pages_ui_translations.json` ويحتوي 206 مفاتيح ترجمة لواجهة صفحات apanel المتخصصة.
- تم تحديث `StudentSystemTranslationSeeder.php` ليقرأ `apanel_pages_ui_translations.json` بالإضافة إلى `apanel_crud_ui_translations.json`.
- تم الحفاظ على تعريفات الحقول والمسارات التقنية داخل React لأنها implementation details، بينما النصوص الظاهرة للمستخدم أصبحت من جدول الترجمات.

## ربط إكمال React Apanel Pages 2026-07-28 بالبيانات

- نصوص واجهة apanel: `translation_keys` و `translation_values`.
- اللغات: `locales`.
- محتوى CMS المدار في الصفحات: جداول المحتوى القائمة مثل `about_pages`, `announcements`, `blogs`, `news`, `videos`, `university_centers`, `green_campus_articles`, `green_campus_stats`, `web_footers`, `menus`, `media`, `newsletter_subscriptions`, و `settings`.
- endpoints العامة والإدارية المستخدمة: `GET /api/v1/translations`, `GET /api/v1/locales`, `GET /api/v1/settings`, `GET/POST/PUT/DELETE /api/v1/apanel/{resource}`, و endpoints `/api/v1/apanel/cms/*`.
- الإدارة من `/apanel/`: صفحات CMS الحالية داخل `/apanel`, مع إدارة النصوص من `/apanel/translations`, `/apanel/translation-keys`, و `/apanel/translation-values`.

## المتبقي بعد إكمال React Apanel Pages 2026-07-28

- لا توجد نتائج في الفحص الموسع للنصوص الثابتة الظاهرة داخل `apps\web\src\features\apanel\pages`.
- لا توجد hardcoded fallback translations مثل `t("key", "fallback")` داخل النطاق.
- لا توجد قوائم لغات ثابتة أو روابط API/storage مكررة داخل النطاق حسب الفحص.
- قد تبقى أسماء حقول تقنية أو route/resource names داخل الكود، وهذا مسموح لأنه implementation logic وليس محتوى CMS قابل للإدارة.

## تحقق إكمال React Apanel Pages 2026-07-28

- فحص `setError("...")`, `setSuccess("...")`, `alert("...")`, `placeholder="..."`, `label="..."`, `title="..."`, `aria-label="..."`, والنصوص المباشرة بين الوسوم: لا توجد نتائج داخل النطاق.
- فحص `t("key", "fallback")`, `fallbackTranslations`, `localhost`, `127.0.0.1`, `VITE_API_BASE_URL`, `/storage/`, وقوائم اللغات الثابتة: لا توجد نتائج داخل النطاق.
- فحص مفاتيح `t("...")`: كل مفاتيح صفحات apanel موجودة في seeder أو ملفات JSON seed.
- `npm.cmd run lint -- --quiet`: نجح.
- `npm.cmd run build`: نجح.
- `php-local.bat -l database\seeders\StudentSystemTranslationSeeder.php`: نجح بدون أخطاء syntax.
- `php-local.bat artisan db:seed --class=StudentSystemTranslationSeeder`: نجح.
- `php-local.bat artisan route:list --path=api/v1 --except-vendor`: نجح وأظهر 170 route.
- `php-local.bat artisan test`: نجح، 4 tests passed و 7 assertions.
- `php-local.bat artisan optimize:clear`: نجح.
- فحص عناوين التقرير: لا توجد عناوين Markdown مكررة.

---

## مراجعة React Apanel Pages Partial 2026-07-28

تاريخ المراجعة: 2026-07-28

## ملفات React Apanel Pages Partial 2026-07-28 التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages`
- `ApanelAboutPage.jsx`
- `ApanelAdministration.jsx`
- `ApanelAnnouncements.jsx`
- `ApanelApplicationDetail.jsx`
- `ApanelApplicationsWorkflow.jsx`
- `ApanelBlog.jsx`
- `ApanelCenters.jsx`
- `ApanelContactManagement.jsx`
- `ApanelContactPage.jsx`
- `ApanelCrud.jsx`
- `ApanelDashboard.jsx`
- `ApanelFooterWeb.jsx`
- `ApanelGreenCampus.jsx`
- `ApanelHeaderNavbar.jsx`
- `ApanelInteractiveServices.jsx`
- `ApanelLocales.jsx`
- `ApanelLogin.jsx`
- `ApanelMedia.jsx`
- `ApanelNewsEvents.jsx`
- `ApanelNewsletterSubscriptions.jsx`
- `ApanelTranslations.jsx`
- `ApanelVideoBdtu.jsx`

## ما تم العثور عليه في React Apanel Pages Partial 2026-07-28

- وجدت labels/placeholders ورسائل إدارية ثابتة داخل صفحات apanel الكبيرة.
- `ApanelCrud.jsx` كان يحتوي schema labels/titles ثابتة كثيرة لواجهة CRUD العامة.
- `ApanelLocales.jsx` و `ApanelTranslations.jsx` كانتا تحتويان نصوص لغة وترجمة ثابتة، ومنها إشارة ثابتة للغات الأربع.
- `ApanelMedia.jsx`, `ApanelNewsletterSubscriptions.jsx`, `ApanelDashboard.jsx`, و `ApanelApplicationDetail.jsx` كانت تحتوي نصوص واجهة أو placeholders ثابتة.

## تغييرات React Apanel Pages Partial 2026-07-28

- تم تحويل `ApanelCrud.jsx` لاستخدام مفاتيح ترجمة في schema بدل النصوص المباشرة، مع طبقة `localizeResourceSchema` التي تمرر `title`, `columns`, `fields`, و option labels عبر `t(...)`.
- تم إنشاء `apps\api\database\data\apanel_crud_ui_translations.json` ويحتوي 247 مفتاح ترجمة أولي لواجهة CRUD العامة.
- تم تعديل `StudentSystemTranslationSeeder.php` ليقرأ مفاتيح `apanel_crud_ui_translations.json` بطريقة آمنة idempotent.
- تم تحويل نصوص `ApanelLocales.jsx`, `ApanelTranslations.jsx`, `ApanelMedia.jsx`, `ApanelNewsletterSubscriptions.jsx`, `ApanelDashboard.jsx`, و placeholders في `ApanelApplicationDetail.jsx` إلى مفاتيح ترجمة من قاعدة البيانات.
- تمت إضافة مفاتيح ترجمة جديدة للغات، إدارة الترجمات، مكتبة الوسائط، اشتراكات النشرة، dashboard، و application detail placeholders.

## ربط React Apanel Pages Partial 2026-07-28 بالبيانات

- النصوص الإدارية: `translation_keys` و `translation_values`.
- اللغات: `locales`.
- إعدادات النظام العامة والشعارات: `settings`.
- ملفات الوسائط: `media`.
- اشتراكات النشرة: `newsletter_subscriptions`.
- لوحة dashboard: endpoint إدارة `/api/v1/apanel/dashboard` عبر `apanelService.dashboard()`.
- CRUD العام: endpoints `GET/POST/PUT/DELETE /api/v1/apanel/{resource}`.
- الإدارة من لوحة التحكم: `/apanel/translations`, `/apanel/translation-keys`, `/apanel/translation-values`, `/apanel/locales`, `/apanel/media`, `/apanel/newsletter/subscriptions`, وموارد `/apanel/{resource}`.

## المتبقي بعد React Apanel Pages Partial 2026-07-28

- لا أستطيع القول إن كل `apps\web\src\features\apanel\pages` أصبح خاليًا تمامًا من النصوص الثابتة في هذه الجولة.
- الفحص الأخير ما زال يظهر placeholders/messages ثابتة في صفحات متخصصة مثل `ApanelAnnouncements.jsx`, `ApanelBlog.jsx`, `ApanelAboutPage.jsx`, `ApanelContactPage.jsx`, `ApanelContactManagement.jsx`, `ApanelVideoBdtu.jsx`, `ApanelHeaderNavbar.jsx`, `ApanelInteractiveServices.jsx`, و `ApanelNewsEvents.jsx`.
- هذه البقايا هي labels/placeholders/toast أو رسائل واجهة إدارية، وليست بيانات public CMS؛ لكنها ما زالت قابلة للتغيير ويجب تحويلها إلى translation keys في الجولة التالية قبل اعتبار كل apanel pages مكتملة.

## تحقق React Apanel Pages Partial 2026-07-28

- فحص static/fallback داخل النطاق: كشف المتبقي المذكور أعلاه، لذلك لم يتم ادعاء اكتمال كل الصفحات.
- فحص مفاتيح الصفحات المعدلة: كل مفاتيح `t("...")` في الملفات المعدلة موجودة في seeder أو JSON seed.
- `npm.cmd run lint -- --quiet`: نجح.
- `npm.cmd run build`: نجح.
- `php-local.bat -l database\seeders\StudentSystemTranslationSeeder.php`: نجح بدون أخطاء syntax.
- `php-local.bat artisan db:seed --class=StudentSystemTranslationSeeder`: نجح عند تشغيله منفردًا. فشل مرة أثناء التشغيل المتوازي بسبب Windows file lock في `bootstrap\cache` ثم نجح بعد الإعادة.
- `php-local.bat artisan route:list --path=api/v1 --except-vendor`: نجح وأظهر 170 route.
- `php-local.bat artisan test`: نجح، 4 tests passed و 7 assertions.
- `php-local.bat artisan optimize:clear`: نجح.
- فحص عناوين التقرير: لا توجد عناوين Markdown مكررة.

---

## مراجعة React Apanel Features 2026-07-28

تاريخ المراجعة: 2026-07-28

## ملفات React Apanel Features 2026-07-28 التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\components`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\layouts`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\utils`

## ما تم العثور عليه في React Apanel Features 2026-07-28

- كان `ApanelLayout.jsx` يحتوي قائمة لغات ثابتة `EN/UZ/RU/AR` ونصوص branding/user ثابتة.
- كانت المكونات المشتركة تحتوي defaults ظاهرة مثل confirm/search/table/pagination/media labels.
- كان `ApanelLogin.jsx` يحتوي branding ونصوص login ثابتة.
- كان `ApanelApplicationDetail.jsx` يولد `contract_number` و `payment_number` باستخدام prefixes ثابتة داخل React.
- لا توجد داخل النطاق روابط `localhost`, `127.0.0.1`, `VITE_API_BASE_URL`, أو `/storage/` مكررة؛ الخدمات ما زالت تمر عبر الطبقة المركزية.

## تغييرات React Apanel Features 2026-07-28

- تم تحويل language selector في `ApanelLayout.jsx` ليستخدم `locales` القادمة من API بدلاً من قائمة ثابتة.
- تم ربط شعار واسم apanel بإعدادات `settings` و `logoSrc` القادمة من `LocaleContext`.
- تم تحويل navigation labels داخل `ApanelLayout.jsx` إلى مفاتيح ترجمة.
- تم تحويل defaults في `ConfirmDialog`, `SearchFilterBar`, `DataTable`, `Pagination`, `MediaPicker`, و `FormBuilder` إلى مفاتيح ترجمة.
- تم تحويل `ApanelLogin.jsx` إلى ترجمات وإعدادات branding بدلاً من نصوص ثابتة.
- تم نقل prefixes الخاصة بأرقام العقود والمدفوعات من React إلى settings: `workflow_contract_prefix`, `workflow_payment_prefix`.
- تم إضافة مفاتيح الترجمة والإعدادات الناقصة في `StudentSystemTranslationSeeder.php` بطريقة safe `firstOrCreate`.

## ربط React Apanel Features 2026-07-28 بالبيانات

- جدول اللغات: `locales`.
- جداول الترجمات: `translation_keys`, `translation_values`.
- جدول إعدادات branding و workflow prefixes: `settings`.
- API اللغات والترجمات: `GET /api/v1/locales`, `GET /api/v1/translations`.
- API إعدادات الموقع: `GET /api/v1/settings`.
- إدارة اللغات والترجمات من `/apanel/locales` و `/apanel/translations`.
- إدارة settings من resource `settings` داخل `/apanel` عبر AdminCrud.
- صفحات CMS نفسها ما زالت تحفظ محتوى الموقع في جداولها الحالية مثل about/contact/header/footer/news/blog/videos/centers حسب endpoints الموجودة.

## المتبقي بعد React Apanel Features 2026-07-28

- لا تزال توجد نصوص UI داخل صفحات apanel الكبيرة مثل form labels, tab labels, placeholders, toast/error messages في صفحات CMS المتخصصة. هذه ليست محتوى الموقع العام، لكنها ما زالت نصوص واجهة داخل الكود ولم يتم تحويلها كلها إلى جدول الترجمات في هذه الجولة.
- بقيت status constants وأسماء resources وfield keys وroute paths لأنها تفاصيل تقنية لازمة.
- لم يتم إنشاء صفحات `/admin` ولم يتم توسيع النطاق خارج `features/apanel` والـ seeder المرتبط بالترجمات/settings.

## تحقق React Apanel Features 2026-07-28

- تم فحص قوائم اللغات الثابتة و `t("key", "fallback")`: لم يظهر fallback translation أو قائمة لغات ثابتة بعد تعديل layout.
- تم فحص `localhost`, `127.0.0.1`, `VITE_API_BASE_URL`, و `/storage/`: لم تظهر نتائج داخل النطاق.
- تم فحص `BSTU-`, `PAY-`, `apanel@bstu.uz`, و branding الثابت في الملفات المعدلة: لم تعد موجودة في React ضمن هذه المواضع.
- تم التحقق أن مفاتيح `t("...")` داخل `features/apanel` موجودة في seeder أو مولدة كـ status keys.
- `npm.cmd run lint -- --quiet` نجح.
- `npm.cmd run build` نجح.
- `php-local.bat -l database\seeders\StudentSystemTranslationSeeder.php` نجح.
- `php-local.bat artisan db:seed --class=StudentSystemTranslationSeeder` نجح.
- `php-local.bat artisan route:list --path=api/v1 --except-vendor` نجح وأظهر 170 route.
- `php-local.bat artisan test` نجح: 4 tests passed, 7 assertions.
- `php-local.bat artisan optimize:clear` نجح.

## المتبقي في Web Public Root Environment

- `index.html` ما زال يحتوي عنصر `<title>` و`lang` كـ bootstrap HTML ضروري قبل تحميل React؛ القيم النهائية يتم ضبطها من قاعدة البيانات بعد تحميل التطبيق.
- `package.json` يحتوي اسم package تقني، وليس نصاً عاماً للموقع أو محتوى يديره apanel.
- `.env` المحلي لم يتم تعديله حفاظاً على إعدادات بيئة جهازك.

---

## مراجعة React Components Context Dynamic Runtime

تاريخ المراجعة: 2026-07-28

## ملفات React Components Context Dynamic Runtime التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\common`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\common\EmptyState.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\common\ErrorBoundary.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\common\ErrorState.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\common\FieldError.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\common\FormError.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\common\LoadingState.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\common\Pagination.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\common\RetryButton.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\Footer.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\Header.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\PageHeader.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components\ScrollToTop.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\context`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\context\LocaleContext.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\context\LanguageContext.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\context\AuthContext.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\context\AppDataContext.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\lib\api.js` كملف مرتبط مباشرة لتوحيد API/storage logic.

## ما تم العثور عليه في React Components Context Dynamic Runtime

- `LocaleContext.jsx` كان يحتوي fallback محلي لرابط API إلى `127.0.0.1`.
- `LocaleContext.jsx` كان يبني روابط storage داخله رغم وجود helper مركزي في `lib/api.js`.
- اتجاه اللغة كان مربوطاً بكود `ar` داخل React بدلاً من قيمة `direction` القادمة من جدول `locales`.
- اختيار الشعار كان مربوطاً بقائمة أكواد ثابتة لبعض اللغات.
- المكونات المشتركة تستخدم مفاتيح ترجمة من قاعدة البيانات ولا تحتوي fallback text داخل `t("key", "text")`.

## تغييرات React Components Context Dynamic Runtime

- تمت إزالة fallback المحلي من `apps/web/src/lib/api.js`، وأصبح `VITE_API_BASE_URL` يأتي من البيئة فقط.
- تم تحسين `publicAssetUrl` في `lib/api.js` ليكون المصدر المركزي الوحيد لبناء روابط ملفات public storage.
- تم تعديل `LocaleContext.jsx` لاستخدام `publicAssetUrl` بدلاً من تكرار منطق storage.
- تم تعديل `LocaleContext.jsx` ليستخدم `locales.direction` من API لتحديد `html dir`, `body dir`, و `isRtl`.
- تم تعديل اختيار الشعار ليستخدم `settings[\`branding_logo_${locale}\`]` ديناميكياً مع `branding_logo_default`.
- تم تعديل `Header.jsx`, `Footer.jsx`, و `PageHeader.jsx` لاستخدام `isRtl` من context بدلاً من شرط ثابت على كود لغة.

## ربط React Components Context Dynamic Runtime بالبيانات

- قاعدة البيانات: جدول `locales` لإدارة اللغات والاتجاه، جدول `translation_keys` و `translation_values` للنصوص، جدول `settings` لإعدادات branding وfavicon وsite metadata، وجداول `web_footers` و `web_footer_translations` لمحتوى الفوتر.
- Laravel API: `/api/v1/locales`, `/api/v1/translations`, `/api/v1/settings/public`, `/api/v1/menus/header`, و `/api/v1/footer-web`.
- `/apanel/`: إدارة اللغات من مورد locales، إدارة الترجمات من apanel translations، إدارة header من `/apanel/cms/header-navbar`، إدارة footer من `/apanel/cms/footer-web`، وإدارة branding/settings من إعدادات CMS.
- نظام اللغات: إضافة لغة جديدة نشطة في جدول `locales` مع `direction` و `sort_order` تجعلها تظهر في `Header` بدون تعديل React، وتستخدم الترجمات الخاصة بها عبر API.

## تحقق React Components Context Dynamic Runtime

- إعادة المسح داخل المجموعة لم تجد `localhost`, `127.0.0.1`, hardcoded language comparisons، أو `t("key", "static fallback")`.
- بقي `VITE_API_BASE_URL` و `/storage` فقط داخل `lib/api.js` لأنه المصدر المركزي المقصود للـ API/storage.
- `npm.cmd run lint` نجح.
- `npm.cmd run build` نجح.
- `php-local.bat artisan route:list --path=api/v1 --except-vendor` نجح وأظهر 170 route.
- `php-local.bat artisan test` نجح: 4 tests passed, 7 assertions.
- `php-local.bat artisan optimize:clear` نجح.

## المتبقي في React Components Context Dynamic Runtime

- توجد رسائل `console.error` تقنية للتطوير وتشخيص فشل API أو render، وليست محتوى واجهة ولا تعرض للمستخدم.
- توجد route names وstorage keys وrole values تقنية مثل `apanel`, `header`, و `action`; هذه ليست محتوى CMS ولا تنقل إلى قاعدة البيانات.

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

---

## متابعة تنظيف Features Apanel Student 2026-07-28

تاريخ المتابعة: 2026-07-28

## ملفات متابعة Features Apanel Student 2026-07-28 التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\components\FormBuilder.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\utils\locales.js`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelCrud.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelAboutPage.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelAdministration.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelAnnouncements.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelApplicationsWorkflow.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelBlog.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelCenters.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelContactPage.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelContactManagement.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelFooterWeb.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelGreenCampus.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelHeaderNavbar.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelInteractiveServices.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelNewsEvents.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelNewsletterSubscriptions.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelVideoBdtu.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\student`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\seeders\StudentSystemTranslationSeeder.php`

## ما تم تنفيذه في متابعة Features Apanel Student 2026-07-28

- تم تحويل رسائل `ApanelCrud.jsx` الخاصة بالتحميل، الإضافة، التعديل، الحذف، الفلاتر، وحوار الحذف إلى مفاتيح ترجمة من قاعدة البيانات.
- تم إضافة helper مشترك `features/apanel/utils/locales.js` لقراءة اللغات النشطة من `LocaleContext` وتحويلها إلى codes/options.
- تم حذف قائمة اللغات الثابتة من `ApanelCrud.jsx` لحقل `translation-values.locale`، وأصبحت الخيارات تأتي من `locales` القادمة من API.
- تم حذف قائمة اللغات الثابتة من `FormBuilder.jsx`، وأصبحت نماذج الحقول المترجمة تعتمد على `locales` من نظام اللغة الديناميكي.
- تم تحويل `ApanelAdministration.jsx` ليبني نماذج profile/settings حسب اللغات النشطة القادمة من API بدل `["en", "uz", "ru", "ar"]`.
- تم تحويل `ApanelAnnouncements.jsx` ليبني item/settings translations حسب اللغات النشطة القادمة من API، وأصبح fallback الخاص بالـ slug/title يعتمد على أول لغة نشطة بدل تثبيت `en`.
- تم تحويل `ApanelAboutPage.jsx`، `ApanelContactPage.jsx`، `ApanelFooterWeb.jsx`، `ApanelHeaderNavbar.jsx`، و `ApanelGreenCampus.jsx` لاستخدام اللغات النشطة القادمة من API في tabs، forms، payloads، link labels، menu item labels، settings translations، article translations، و stat translations.
- تم تحويل `ApanelBlog.jsx`، `ApanelCenters.jsx`، `ApanelInteractiveServices.jsx`، `ApanelNewsEvents.jsx`، و `ApanelVideoBdtu.jsx` في نفس اتجاه اللغات الديناميكية، وأصبحت تعتمد على أول لغة نشطة من API بدل تثبيت `en`.
- تم حذف fallback النصي `"Menu Item"` من `ApanelHeaderNavbar.jsx` عند تجهيز payload، حتى لا يتم إخفاء نقص الترجمة بنص ثابت داخل الكود.
- تم حذف `console.log` الخاص بحفظ payload الهيدر من `ApanelHeaderNavbar.jsx`.
- تم إزالة تثبيت `Intl.DateTimeFormat("en")` من صفحات apanel التي تعرض تواريخ داخل النطاق، وأصبح التنسيق يستخدم اللغة النشطة أو أول لغة قادمة من API.
- ملاحظة مهمة: تحويل اللغات في `ApanelAdministration.jsx` و `ApanelAnnouncements.jsx` لا يعني أن كل labels الإدارية داخلهما أصبحت مترجمة؛ ما زالت هناك عناوين وأزرار إدارية ثابتة تحتاج جولة ترجمة منفصلة.
- تم تحويل رسائل `FormBuilder.jsx` العامة مثل أخطاء JSON، أخطاء validation، زر الإلغاء، وزر إنشاء/تحديث السجل إلى مفاتيح ترجمة.
- تم تحويل معظم رسائل وأزرار وحقول `ApanelApplicationsWorkflow.jsx` إلى مفاتيح ترجمة بدلاً من نصوص ثابتة، خصوصاً:
  - عناوين مراحل workflow.
  - أزرار approve/reject/download/issue.
  - prompts الخاصة بالرفض والتصحيح والسكن والتلكس والإقامة.
  - رسائل النجاح والفشل داخل workflow.
  - labels الخاصة بالمراجعة النهائية والقبول والتسجيل والأمر ورسوم الخدمة.
- تمت إضافة مفاتيح الترجمة الجديدة في `StudentSystemTranslationSeeder.php` بطريقة آمنة تستخدم `firstOrCreate` ولا تحذف أو تستبدل تعديلات apanel الحالية.

## ربط متابعة Features Apanel Student 2026-07-28 بالبيانات

- جدول اللغات: `locales`.
- جدول مفاتيح الترجمة: `translation_keys`.
- جدول قيم الترجمة: `translation_values`.
- API اللغات: `GET /api/v1/locales`.
- API الترجمات: `GET /api/v1/translations`.
- إدارة اللغات من apanel: `/apanel/locales`.
- إدارة الترجمات من apanel: `/apanel/translations`.
- إدارة موارد CRUD العامة من apanel: `/apanel/{resource}`.
- إدارة سير الطلبات من apanel: `/apanel/applications/*`.

## المتبقي بعد متابعة Features Apanel Student 2026-07-28

- تم فحص `features/apanel` و `features/student` ولم تعد تظهر أنماط قوائم اللغات الثابتة `en/uz/ru/ar` أو `translations.en` أو `activeLocale === "en"` داخل النطاق المحدد.
- لا أستطيع اعتبار كل `features/apanel` منتهياً بالكامل من ناحية النصوص الإدارية؛ ما زالت توجد labels وأزرار ورسائل إدارية ثابتة في بعض صفحات apanel. هذه ليست محتوى الموقع العام، لكنها نصوص واجهة قابلة للترجمة ويجب نقلها تدريجياً إلى `translation_keys` و `translation_values`.
- تمت إزالة placeholders الإرشادية التي كانت تحتوي أمثلة روابط صور من `ApanelBlog.jsx` و `ApanelNewsEvents.jsx`.
- ما زالت توجد labels وأزرار إدارية ثابتة داخل بعض صفحات apanel إذا كان الهدف ترجمة لوحة التحكم بالكامل، لكنها ليست مصدر محتوى الموقع العام ولا تبني بيانات CMS ثابتة للموقع.
- لم يتم العثور على URL API مكرر أو `localhost` أو `127.0.0.1` داخل النطاق المحدد.

## تحقق متابعة Features Apanel Student 2026-07-28

- `npm.cmd run lint` نجح بدون أخطاء.
- `npm.cmd run build` نجح.
- `php-local.bat -l database\seeders\StudentSystemTranslationSeeder.php` نجح.
- `php-local.bat artisan db:seed --class=StudentSystemTranslationSeeder` نجح.
- `php-local.bat artisan route:list --path=api/v1 --except-vendor` نجح وأظهر 170 route.
- `php-local.bat artisan test` نجح: 4 tests passed, 7 assertions.
- `php-local.bat artisan optimize:clear` نجح.
- تم حذف ملف temp cache الناتج من تعارض Laravel: `apps/api/bootstrap/cache/serB77E.tmp`.
- تم إعادة فحص `features/apanel` و `features/student` بعد التعديلات ولم تظهر قوائم locale ثابتة أو hardcoded `translations.en`.
- تم فحص `t("key", "fallback")` داخل النطاق ولم تظهر fallbacks نصية من هذا النوع.
- تم فحص `Intl.DateTimeFormat("en")` داخل النطاق ولم تظهر نتائج بعد التعديل.
- تم فحص `localhost` و `127.0.0.1` و `VITE_API_BASE_URL` و `/storage/` و `http://` و `https://` داخل `apps/web/src/features` ولم تظهر نتائج بعد إزالة placeholders.

---

## مراجعة تجميعية Api Web Core Paths 2026-07-28

تاريخ المراجعة: 2026-07-28

## ملفات مراجعة Api Web Core Paths 2026-07-28 التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\.agents`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\.vscode`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\.github`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\app`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\bootstrap`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\config`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\public`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\resources`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\routes`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\tests`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api` root config files
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\public`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\components`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\context`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\pages`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\sections`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web` root config files
- `node_modules`, `vendor`, و `apps\web\dist` تمت مراجعتها كتصنيف مولد أو طرف ثالث، وليست مصدر محتوى CMS.

## ما تم العثور عليه في مراجعة Api Web Core Paths 2026-07-28

- لم تظهر روابط API ثابتة أو `localhost` أو `127.0.0.1` أو `/storage/` مكررة داخل كود المشروع بعد استبعاد `node_modules`, `vendor`, و `dist`.
- لم تظهر fallbacks من نوع `t("key", "Static text")` داخل النطاق المفحوص.
- لم تظهر قوائم لغات ثابتة `en/uz/ru/ar` داخل كود React الذي تمت مراجعته سابقاً.
- `apps/api/.env` يحتوي إعدادات بيئة مثل `APP_KEY`، وهي أسرار/إعدادات تشغيل وليست محتوى CMS ولا يجب نقلها إلى قاعدة البيانات.
- `vendor`, `node_modules`, و `dist` ليست مصادر محتوى قابلة للإدارة من `/apanel/`; هي dependencies أو build output ويمكن إعادة توليدها.
- `apps/api/storage/temp` يحتوي ملفات تشغيل مؤقتة ينتجها `php-local` و Symfony أثناء الاختبارات.

## تغييرات مراجعة Api Web Core Paths 2026-07-28

- تم إضافة `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\storage\temp\.gitignore` لمنع دخول ملفات temp المؤقتة إلى المشروع.
- بقي إصلاح `ApanelCenters.jsx` السابق ضمن نفس الجولة: تحويل `load` إلى `useCallback` وإصلاح تحذيرات Tailwind canonical classes.

## ربط مراجعة Api Web Core Paths 2026-07-28 بالبيانات

- المحتوى العام والمدخلات القابلة للإدارة تعتمد على جداول CMS القائمة حسب المجال، مثل pages, page blocks, menus, settings, translations, locales, news, blogs, announcements, centers, videos, applications.
- جدول اللغات: `locales`.
- جداول الترجمة: `translation_keys`, `translation_values`.
- API اللغات والترجمات: `GET /api/v1/locales`, `GET /api/v1/translations`.
- إدارة اللغة والترجمة من `/apanel/locales` و `/apanel/translations`.
- إدارة محتوى CMS من endpoints الموجودة تحت `/api/v1/apanel/cms/*` و `/api/v1/apanel/{resource}`.

## المتبقي بعد مراجعة Api Web Core Paths 2026-07-28

- لم يتم نقل إعدادات تقنية مثل `.env`, config files, package files, workflows, أو bootstrap files إلى قاعدة البيانات لأنها ليست محتوى يديره المدير من `/apanel/`.
- لا يتم تعديل `vendor`, `node_modules`, أو `dist` كمصدر CMS؛ إذا احتجنا تنظيفها فيتم حذفها وإعادة توليدها بالأوامر المناسبة، وليس تحويل محتواها إلى قاعدة البيانات.
- لم أحذف ملفات `storage/temp` مباشرة لأن أمر الحذف رُفض من سياسة الأداة، لكن تمت إضافة `.gitignore` للمجلد لمنع تتبع هذه الملفات المؤقتة.

## تحقق مراجعة Api Web Core Paths 2026-07-28

- `npm.cmd run lint -- --quiet` نجح.
- `npm.cmd run build` نجح.
- `php-local.bat artisan route:list --path=api/v1 --except-vendor` نجح وأظهر 170 route.
- `php-local.bat artisan test` نجح: 4 tests passed, 7 assertions.
- `php-local.bat artisan optimize:clear` نجح.
- `php-local.bat -l database\seeders\StudentSystemTranslationSeeder.php` نجح.

---

## مراجعة React Lib Services Utils 2026-07-28

تاريخ المراجعة: 2026-07-28

## ملفات React Lib Services Utils 2026-07-28 التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\utils`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\utils\cmsContent.js`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\main.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\index.css`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\App.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\services`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\services\*.js`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\lib`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\lib\*.js`

## ما تم العثور عليه في React Lib Services Utils 2026-07-28

- لم تظهر بيانات CMS أو mock/demo arrays أو fallbacks من نوع `t("key", "Static text")` داخل النطاق المفحوص.
- `apps\web\src\lib\api.js` هو المصدر المركزي الصحيح لـ `VITE_API_BASE_URL`, headers, locale query, auth token, storage URL building, و file download.
- `apps\web\src\index.css` يحتوي import خطوط Google و selectors فنية للـ fonts والاتجاهات، وليست محتوى CMS قابل للإدارة.
- `apps\web\src\App.jsx` يحتوي route definitions تقنية فقط، ويستخدم `/apanel/` و `/student/` ولا ينشئ صفحات تحت `/admin`.

## تغييرات React Lib Services Utils 2026-07-28

- تم تعديل `apps\web\src\utils\cmsContent.js` ليستخدم `publicAssetUrl` من `apps\web\src\lib\api.js` بدلاً من بناء `/storage` داخل helper منفصل.
- تم تعديل `apps\web\src\services\applicationService.js` ليستخدم `downloadBlob` من `apps\web\src\lib\api.js` عند تنزيل مستند الطالب بدلاً من تنفيذ تنزيل غير مركزي.

## ربط React Lib Services Utils 2026-07-28 بالبيانات

- جداول اللغات والترجمات: `locales`, `translation_keys`, `translation_values`.
- API اللغات والترجمات: `GET /api/v1/locales`, `GET /api/v1/translations`.
- إدارة اللغات والترجمات من `/apanel/locales` و `/apanel/translations`.
- خدمات React داخل `apps\web\src\services` تعتمد على Laravel API من خلال `apps\web\src\lib\api.js`، ولا تحتوي مصدر بيانات CMS مستقل.
- مسارات الملفات العامة تمر عبر `publicAssetUrl` المركزي، وتنزيل الملفات يمر عبر `downloadBlob` المركزي.

## المتبقي بعد React Lib Services Utils 2026-07-28

- لم يتم نقل endpoint names أو route definitions أو storage keys أو CSS/font rules إلى قاعدة البيانات لأنها تفاصيل تنفيذ تقنية وليست محتوى يديره المدير.
- لم أجد داخل هذه المجموعة محتوى قابل للإدارة يحتاج جدولاً جديداً أو صفحة apanel جديدة.

## تحقق React Lib Services Utils 2026-07-28

- تم فحص `fetch`, `VITE_API_BASE_URL`, `/storage/`, و `downloadBlob`: الاستخدام المركزي موجود في `apps\web\src\lib\api.js` فقط، مع استدعاءات خدمات مسموحة للدوال المركزية.
- تم فحص `t("key", "fallback")`, `fallbackTranslations`, قوائم اللغات الثابتة، و `Intl.DateTimeFormat("en")`: لم تظهر نتائج داخل النطاق.
- `npm.cmd run lint -- --quiet` نجح.
- `npm.cmd run build` نجح.
- `php-local.bat artisan route:list --path=api/v1 --except-vendor` نجح وأظهر 170 route.
- `php-local.bat artisan test` نجح: 4 tests passed, 7 assertions.
- `php-local.bat artisan optimize:clear` نجح.
- لم يتم تعديل PHP ضمن هذه المجموعة، لذلك لم تكن هناك ملفات PHP جديدة تحتاج `php -l`.

---

## إعادة تحقق React Apanel Pages 2026-07-28

تاريخ إعادة التحقق: 2026-07-28

## ملفات إعادة تحقق React Apanel Pages 2026-07-28 التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelAboutPage.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelAdministration.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelAnnouncements.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelApplicationDetail.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelApplicationsWorkflow.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelBlog.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelCenters.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelContactManagement.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelContactPage.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelCrud.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelDashboard.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelFooterWeb.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelGreenCampus.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelHeaderNavbar.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelInteractiveServices.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelLocales.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelLogin.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelMedia.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelNewsEvents.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelNewsletterSubscriptions.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelTranslations.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\apanel\pages\ApanelVideoBdtu.jsx`

## نتيجة إعادة تحقق React Apanel Pages 2026-07-28

- لم تظهر نصوص واجهة ثابتة جديدة داخل صفحات apanel المحددة بعد تحويل الجولة السابقة.
- لم تظهر fallbacks من نوع `t("key", "Static text")` داخل النطاق.
- لم تظهر قوائم لغات ثابتة أو استخدامات `translations.en` داخل النطاق.
- لم تظهر روابط API أو storage مكررة مثل `localhost`, `127.0.0.1`, `VITE_API_BASE_URL`, أو `/storage/` داخل النطاق.
- لا توجد تعديلات مصدر جديدة مطلوبة في هذه الإعادة؛ التعديل الوحيد في هذه الجولة هو توثيق نتيجة إعادة التحقق في هذا التقرير.

## ربط إعادة تحقق React Apanel Pages 2026-07-28 بالبيانات

- نصوص واجهة apanel: `translation_keys`, `translation_values`.
- اللغات الديناميكية: `locales`.
- مفاتيح واجهة CRUD العامة: `apps\api\database\data\apanel_crud_ui_translations.json` محملة عبر `StudentSystemTranslationSeeder.php`.
- مفاتيح واجهة صفحات apanel المتخصصة: `apps\api\database\data\apanel_pages_ui_translations.json` محملة عبر `StudentSystemTranslationSeeder.php`.
- API اللغات والترجمات: `GET /api/v1/locales`, `GET /api/v1/translations`.
- API إدارة محتوى apanel العام: endpoints الموجودة تحت `/api/v1/apanel/*`.
- إدارة اللغات والترجمات من `/apanel/locales` و `/apanel/translations`.
- إدارة محتوى الصفحات المتخصصة من صفحات `/apanel` القائمة مثل centers, blog, news-events, announcements, green-campus, footer, contact, video-bdtu, administration, about-page, و interactive-services.

## تحقق إعادة React Apanel Pages 2026-07-28

- إعادة فحص النصوص الظاهرة الثابتة و `label`, `title`, `placeholder`, `aria-label`, `alert`, `confirm`, و `prompt`: لم تظهر نتائج داخل `apps\web\src\features\apanel\pages`.
- إعادة فحص hardcoded fallbacks واللغات والروابط المكررة: لم تظهر نتائج داخل `apps\web\src\features\apanel\pages`.
- `npm.cmd run lint -- --quiet` نجح.
- `npm.cmd run build` نجح.
- `php-local.bat -l database\seeders\StudentSystemTranslationSeeder.php` نجح.
- `php-local.bat artisan route:list --path=api/v1 --except-vendor` نجح وأظهر 170 route.
- `php-local.bat artisan test` نجح: 4 tests passed, 7 assertions.
- `php-local.bat artisan optimize:clear` نجح.

## المتبقي بعد إعادة تحقق React Apanel Pages 2026-07-28

- لا يوجد محتوى واجهة قابل للإدارة تم رصده كقيمة ثابتة داخل ملفات `apps\web\src\features\apanel\pages` المحددة.
- بقيت أسماء resources وroute paths وstatus codes التقنية داخل المنطق البرمجي فقط، وليست محتوى CMS.

---

## مراجعة React Student Features 2026-07-28

تاريخ المراجعة: 2026-07-28

## ملفات React Student Features 2026-07-28 التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\student`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\student\layouts`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\student\layouts\StudentLayout.jsx`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\student\pages`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\web\src\features\student\pages\*.jsx`

## ما تم العثور عليه في React Student Features 2026-07-28

- كانت توجد نصوص واجهة طالب ثابتة داخل صفحات الطالب: loading, errors, success messages, labels, empty states, prompts, placeholders, وأزرار.
- كان `StudentLayout.jsx` يحتوي قائمة لغات ثابتة `EN/UZ/RU/AR` داخل select الطالب.
- كان يوجد fallback نصي داخل `t("key", "Static text")` في صفحات الطالب.
- كانت توجد بعض fallbacks ظاهرة مثل `Not Started`, `Student User`, و currency fallback داخل الكود.
- كانت توجد قيمة branding ظاهرة `BSTU` ومثال بريد ثابت داخل login/register.
- كانت `StudentApplication.jsx` تحتوي قائمة ثابتة لأنواع المستندات المطلوبة قبل إرسال الطلب.

## تغييرات React Student Features 2026-07-28

- تم تحويل language selector في `StudentLayout.jsx` ليستخدم `locales` القادمة من `LocaleContext` عبر API بدلاً من قائمة لغات ثابتة.
- تم تحويل نصوص واجهة الطالب داخل dashboard, login, register, profile, application, documents, status, contracts, payments, notifications, support, و phase-two إلى مفاتيح ترجمة.
- تم حذف fallbacks النصية من `t("key", "Static text")` داخل النطاق.
- تم إزالة بناء نصوص افتراضية تخفي نقص البيانات مثل `Not Started` و fallback currency.
- تم ربط شعار/اسم بوابة الطالب بإعدادات الموقع القادمة من `LocaleContext` عبر API بدلاً من نص branding ثابت داخل `StudentLayout.jsx`.
- تم تحويل مثال البريد في login/register إلى مفتاح ترجمة `form.emailPlaceholder`.
- تم ربط تحقق المستندات المطلوبة في `StudentApplication.jsx` بـ `studentPortalService.documents()` بدلاً من قائمة document types ثابتة داخل React.
- تمت إضافة `document.requirementsUnavailable` لإظهار خطأ قابل للكشف عند غياب checklist من API بدلاً من استعمال قائمة بديلة مخفية داخل الكود.
- تم إضافة مفاتيح الترجمة الجديدة في `StudentSystemTranslationSeeder.php` بطريقة آمنة باستخدام نفس `firstOrCreate` الموجود، وتم تشغيل seeder بدون حذف أو overwrite لبيانات apanel الحالية.

## ربط React Student Features 2026-07-28 بالبيانات

- جدول اللغات: `locales`.
- جداول الترجمات: `translation_keys`, `translation_values`.
- جدول إعدادات الموقع والbranding: `settings`.
- متطلبات مستندات الطلب: جداول application/document workflow الموجودة التي يغذيها endpoint checklist الخاص بالطالب.
- API اللغات والترجمات: `GET /api/v1/locales`, `GET /api/v1/translations`.
- API إعدادات الموقع والbranding: `GET /api/v1/settings`.
- API متطلبات مستندات الطالب: `GET /api/v1/student/documents/checklist`.
- إدارة اللغات والترجمات من `/apanel/locales` و `/apanel/translations`.
- إدارة إعدادات الموقع والbranding من صفحات إعدادات/branding الموجودة في `/apanel`.
- بيانات الطالب والطلبات والملفات والمدفوعات تأتي من Laravel API عبر endpoints الموجودة تحت:
  - `/api/v1/student/*`
  - `/api/v1/applications/*`
  - `/api/v1/auth/*`
- إدارة سير الطلب والملفات والمدفوعات من `/apanel/applications` و endpoints workflow الموجودة تحت `/api/v1/apanel/applications-workflow/*`.

## المتبقي بعد React Student Features 2026-07-28

- بقيت status codes وقيم تقنية داخل الشروط مثل `APPROVED`, `REJECTED`, و route paths؛ هذه ليست محتوى CMS بل منطق حالة مطلوب.
- بقيت رسائل `console.error` للمطورين وليست نصوص واجهة للمستخدم.
- لم يتم إنشاء جداول جديدة لأن النظام المناسب موجود بالفعل: `locales`, `translation_keys`, `translation_values`, وجداول student/application الحالية.

## تحقق React Student Features 2026-07-28

- تم فحص `t("key", "fallback")`: لم تظهر نتائج داخل `apps\web\src\features\student`.
- تم فحص قوائم اللغات الثابتة و `translations.en` و `Intl.DateTimeFormat("en")`: لم تظهر نتائج داخل النطاق.
- تم فحص `localhost`, `127.0.0.1`, `VITE_API_BASE_URL`, و `/storage/`: لم تظهر نتائج داخل النطاق.
- تم فحص `student@example.com`, `BSTU`, و قائمة `requiredTypes = [`: لم تظهر نتائج داخل النطاق بعد التعديل.
- تم التحقق أن جميع مفاتيح `t("...")` المستخدمة في `apps\web\src\features\student` موجودة في seeder أو مولدة ضمن status seeder.
- `php-local.bat -l database\seeders\StudentSystemTranslationSeeder.php` نجح.
- `php-local.bat artisan db:seed --class=StudentSystemTranslationSeeder` نجح.
- `npm.cmd run lint -- --quiet` نجح.
- `npm.cmd run build` نجح.
- `php-local.bat artisan route:list --path=api/v1 --except-vendor` نجح وأظهر 170 route.
- `php-local.bat artisan test` نجح: 4 tests passed, 7 assertions.
- `php-local.bat artisan optimize:clear` نجح.

---

## إعادة تحقق Laravel Database Migrations Safety 2026-07-29

تاريخ إعادة التحقق: 2026-07-29

## ملفات إعادة تحقق Laravel Database Migrations Safety 2026-07-29 التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\data`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\migrations`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\seeders`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\factories`

## ما تم العثور عليه في إعادة تحقق Laravel Database Migrations Safety 2026-07-29

- كانت migration `2026_07_16_000005_remove_legacy_footer_translation_keys.php` تحذف مفاتيح ترجمة footer legacy من قاعدة البيانات.
- كانت migration `2026_07_20_000001_expand_green_campus_cms.php` تستخدم تحديثًا عامًا قد يغير `published_at` عند ملء `category`.
- كانت migration `2026_07_23_000002_create_about_page_content_entry_tables.php` تسقط جداول content entries إن وجدت، ثم تصفر `about_page_translations.content` بعد النقل.
- لم تظهر روابط `localhost`, `127.0.0.1`, `/admin`, `VITE_API_BASE_URL`, `/storage/`, `mockData`, أو image fallback داخل نطاق `apps\api\database` بعد التنظيف السابق.

## تغييرات إعادة تحقق Laravel Database Migrations Safety 2026-07-29

- تم تحويل migration حذف مفاتيح footer legacy إلى no-op يحافظ على الترجمات الموجودة.
- تم فصل تحديث `green_campus_articles.category` عن `published_at` حتى لا يتم تعديل `published_at` إلا عندما يكون فارغًا.
- تم تغيير إدخال `green_campus_settings` الافتراضي إلى insert عند الغياب فقط، بدلاً من overwrite.
- تم جعل migration about content entries تنشئ الجداول فقط إذا كانت غير موجودة، وتتجنب إعادة النقل إذا وُجدت entries مسبقًا.
- تم منع تصفير `about_page_translations.content` للحفاظ على النسخة القديمة كمرآة/مصدر احتياطي تقني بدل فقدانها.

## ربط إعادة تحقق Laravel Database Migrations Safety 2026-07-29 بالبيانات

- Footer translations: `translation_keys`, `translation_values`, وتدار من `/apanel/translations`.
- Green campus settings/articles: `green_campus_articles`, `green_campus_settings`, `green_campus_setting_translations`, API `GET /api/v1/green-campus/*`, وإدارة `/apanel/green-campus`.
- About page structured content: `about_pages`, `about_page_translations`, `about_page_content_entries`, `about_page_content_entry_translations`, API `GET /api/v1/about-page`, وإدارة `/apanel/about-page`.

## المتبقي بعد إعادة تحقق Laravel Database Migrations Safety 2026-07-29

- لا توجد عمليات حذف أو تصفير بيانات داخل `up()` للملفات الثلاثة التي تم تعديلها.
- بقيت `Schema::dropIfExists` داخل دوال `down()` في migrations متعددة. لم يتم تغييرها لأنها مسار rollback قياسي، وليست تشغيلًا عاديًا أثناء migration forward.
- بقيت عبارات محتوى أكاديمي تحتوي كلمات مثل `Faculty members` داخل بيانات seed؛ هذه محتوى CMS أولي قابل للإدارة وليست fallback.

## تحقق إعادة Laravel Database Migrations Safety 2026-07-29

- إعادة فحص روابط التطوير، `/admin`, `mockData`, و image fallback داخل `apps\api\database`: لا توجد نتائج ذات صلة.
- إعادة فحص قوائم اللغات الثابتة داخل `apps\api\database`: لا توجد نتائج.
- إعادة فحص `updateOrCreate`, `truncate`, `delete`, وتعطيل `is_active` داخل seeders/migrations: لا توجد نتائج خطرة داخل `up()` بعد التعديل؛ نتائج `dropIfExists` المتبقية في `down()` فقط.
- `php-local.bat -l` نجح لكل ملفات PHP المعدلة داخل `apps\api\database`.
- `php-local.bat artisan test` نجح: 4 tests passed, 7 assertions.
- `php-local.bat artisan route:list --path=api/v1` نجح وأظهر 170 route.
- `npm.cmd run lint` نجح مع تحذيرين قديمين خارج نطاق هذه الجولة في student React files.
- `npm.cmd run build` نجح.
- `php-local.bat artisan optimize:clear` نجح.

---

## إعادة تحقق نهائية Laravel Database Scope 2026-07-29

تاريخ إعادة التحقق: 2026-07-29

## المسارات التي تمت مراجعتها في إعادة التحقق النهائية

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\data`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\factories`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\migrations`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\seeders`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\seeders\Concerns`

## نتيجة إعادة التحقق النهائية

- لم تظهر بقايا `translations['en']`, قائمة لغات ثابتة، أو `locale === 'en'` داخل seeders/migrations بعد التعديلات.
- لم تظهر روابط تطوير، `/admin`, `VITE_API_BASE_URL`, `/storage/`, `mockData`, أو مفاتيح fallback image داخل `apps\api\database`.
- لم تظهر عمليات `updateOrCreate` أو `truncate` داخل seeders/migrations/factories في النطاق المراجع.
- ملفات JSON داخل `apps\api\database\data` صالحة نحويًا.

## التخزين والإدارة

- اللغات مخزنة في `locales` وتدار من `/apanel/locales` وتعرض عبر `GET /api/v1/locales`.
- الترجمات مخزنة في `translation_keys` و`translation_values` وتدار من `/apanel/translations` وتعرض عبر `GET /api/v1/translations`.
- المحتوى العام والأكاديمي والطالب يستخدم جداول CMS/academic/student الموجودة في migrations، ويعرض عبر مسارات `/api/v1/*` العامة ومسارات الطالب، ويدار عبر `/apanel/` resources وCMS settings.

## المتبقي

- سجلات اللغات الأولية داخل `LocaleSeeder.php` بقيت كبيانات seed قابلة للإدارة وليست قائمة ثابتة في منطق runtime.
- تم نقل قيم seed الأولية المطلوبة من schema للبرامج إلى `programs.json` حتى لا تبقى مضمنة داخل `ProgramSeeder.php`.
- دوال `down()` في migrations ما زالت تحتوي rollback drops قياسية، ولم يتم اعتبارها حذفًا تشغيليًا في مسار `up()`.

## فحوص إعادة التحقق النهائية

- إعادة مسح hardcoded language source/fallback داخل seeders/migrations: نجح بلا نتائج.
- إعادة مسح روابط التطوير وfallback/media URL داخل `apps\api\database`: نجح بلا نتائج.
- إعادة مسح `updateOrCreate` و`truncate`: نجح بلا نتائج.
- JSON validation لملفات `apps\api\database\data\*.json`: نجح.
- PHP syntax للملفات المعدلة داخل `apps\api\database`: نجح.
- `php-local.bat artisan test`: نجح، 4 tests passed و7 assertions.
- `php-local.bat artisan route:list --path=api/v1`: نجح وأظهر 170 route.
- `php-local.bat artisan optimize:clear`: نجح.
- `npm.cmd run lint`: نجح مع تحذيرين قديمين خارج نطاق قاعدة البيانات في ملفات student React.
- `npm.cmd run build`: نجح.

---

## إعادة تحقق Laravel Database Seed Locale Source 2026-07-29

تاريخ إعادة التحقق: 2026-07-29

## ملفات إعادة تحقق Laravel Database Seed Locale Source 2026-07-29 التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\data`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\migrations`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\seeders`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\factories\UserFactory.php`

## ما تم العثور عليه في إعادة تحقق Laravel Database Seed Locale Source 2026-07-29

- كان `TranslationKeySeeder.php` يعتمد على `translations['en']` فقط لاكتشاف مفاتيح الترجمة.
- كان `StaffSeeder.php` يستخدم الإنجليزية كمصدر ثابت لبعض بيانات seed، وكان يولد بريدًا وهاتفًا افتراضيين عند غياب المصدر الحقيقي.
- كانت seeders الأكاديمية `CourseSeeder.php`, `DepartmentSeeder.php`, و `ProgramSeeder.php` تستخدم `locale === 'en'` كلغة مصدر للبيانات المفقودة.
- ظهرت `en` المتبقية فقط داخل `LocaleSeeder.php` كبيانات أولية لإنشاء سجل اللغة الإنجليزية، وليس كقائمة لغات ثابتة أو fallback runtime.

## تغييرات إعادة تحقق Laravel Database Seed Locale Source 2026-07-29

- تم توسيع `ResolvesSeedLocales` بدالة `sourceSeedLocale()` لاختيار أول لغة نشطة متاحة من قاعدة البيانات أو أول لغة موجودة في ملف الترجمات.
- تم تعديل `TranslationKeySeeder.php` ليكتشف مفاتيح الترجمة من كل اللغات المتاحة داخل `translations.json` بدلاً من الإنجليزية فقط.
- تم تعديل `StaffSeeder.php` ليستخدم لغة المصدر الديناميكية، ويعتمد على `slug` بدل بريد مولد، ولا ينشئ هاتفًا أو بريدًا افتراضيًا عند غياب البيانات.
- تم تعديل `CourseSeeder.php`, `DepartmentSeeder.php`, و `ProgramSeeder.php` لاستخدام `sourceSeedLocale` بدلاً من `locale === 'en'`.
- تم إزالة fallback الإنجليزي المباشر من `AcademicDepartmentDetailsSeeder.php` وشرط اللغة الزائد في `TechnologyFacultyContentSeeder.php`.

## ربط إعادة تحقق Laravel Database Seed Locale Source 2026-07-29 بالبيانات

- اللغات: جدول `locales`, API `GET /api/v1/locales`, وإدارة `/apanel/locales`.
- الترجمات: `translation_keys`, `translation_values`, API `GET /api/v1/translations`, وإدارة `/apanel/translations`.
- الكادر: `staff_profiles`, `staff_profile_translations`, API `GET /api/v1/staff`, وإدارة staff/admin resources من `/apanel`.
- الكليات والأقسام والبرامج والمقررات: جداول academic structure وtranslations التابعة لها، API public تحت `/api/v1/faculties`, `/api/v1/departments`, `/api/v1/programs`, `/api/v1/courses`, وإدارة `/apanel` للموارد الأكاديمية.

## المتبقي بعد إعادة تحقق Laravel Database Seed Locale Source 2026-07-29

- تم ملء قيم seed الأولية داخل `programs.json` لحقول `studyMode`, `languageOfStudy`, `tuitionFee`, و`currency` لكل البرامج، وأصبح `ProgramSeeder.php` يقرأها من ملف البيانات بدلاً من تثبيتها داخل الكود.
- بيانات الاتصال الحقيقية الموجودة داخل ملفات JSON أو seeders بقيت لأنها محتوى أولي قابل للإدارة وليست fallback مولد.

## تحقق إعادة Laravel Database Seed Locale Source 2026-07-29

- JSON validation لكل ملفات `apps\api\database\data\*.json`: نجح.
- فحص `translations['en']`, قوائم اللغات الثابتة، و `locale === 'en'`: لا توجد نتائج داخل seeders/migrations باستثناء سجل `LocaleSeeder` الخاص بإنشاء اللغة الإنجليزية.
- فحص روابط التطوير وfallback keys: لا توجد نتائج ذات صلة داخل `apps\api\database`.
- `php-local.bat -l` نجح لكل ملفات PHP المعدلة داخل `apps\api\database`.
- `php-local.bat artisan test` نجح: 4 tests passed, 7 assertions. ظهرت رسالة قفل ملف من بيئة Windows بعد النجاح، ولم تغير exit code.
- `php-local.bat artisan route:list --path=api/v1` نجح وأظهر 170 route.
- `npm.cmd run lint` نجح مع تحذيرين قديمين خارج نطاق هذه الجولة في student React files.
- `npm.cmd run build` نجح.
- `php-local.bat artisan optimize:clear` نجح.

---

## تثبيت قيم Program Seed غير الفارغة 2026-07-29

تاريخ المراجعة: 2026-07-29

## المسارات التي تمت مراجعتها

- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\data\programs.json`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\seeders\ProgramSeeder.php`
- `C:\Users\KAPAKA\Desktop\international.bstu.uz\apps\api\database\migrations\2026_07_12_132822_create_academic_structure_tables.php`

## ما تم العثور عليه

- جدول `programs` يحتوي حقولًا غير nullable: `study_mode`, `language_of_study`, `tuition_fee`, و`currency`.
- كانت القيم الأولية لهذه الحقول موجودة داخل `ProgramSeeder.php` مباشرة، وهذا يجعلها تبدو كبيانات ثابتة داخل الكود بدلاً من بيانات seed قابلة للمراجعة.

## التغييرات المنفذة

- تم ملء `studyMode`, `languageOfStudy`, `tuitionFee`, و`currency` داخل `programs.json` لكل البرامج الـ58.
- تم تعديل `ProgramSeeder.php` ليقرأ هذه القيم من `programs.json` ويترك البرنامج غير مبذور إذا غابت القيم المطلوبة، بدلاً من إنشاء fallback مخفي داخل الكود.
- تم استخدام قيم أولية حسب الدرجة: Bachelor = `3500`, Master = `4500`, PhD = `5500`, والعملة `USD`.

## التخزين وواجهة الإدارة

- القيم تحفظ في جدول `programs`.
- تعرض عبر `GET /api/v1/programs` و`GET /api/v1/programs/{slug}`.
- تدار من `/apanel/programs` عبر موارد `/api/v1/apanel/{resource}`.

## الفحوص المنفذة

- تحقق JSON لـ `programs.json`: نجح، 58 برنامجًا ولا توجد قيم ناقصة.
- فحص بقاء قيم الدراسة/اللغة/الرسوم/العملة داخل `ProgramSeeder.php`: نجح، لم تعد القيم مضمنة.
- PHP syntax لـ `ProgramSeeder.php`: نجح.
- إعادة مسح hardcoded language source/fallback وروابط التطوير داخل `apps\api\database`: نجح بلا نتائج.
- `php-local.bat artisan test`: نجح، 4 tests passed و7 assertions.
- `php-local.bat artisan route:list --path=api/v1`: نجح وأظهر 170 route.
- `php-local.bat artisan optimize:clear`: نجح.
- `npm.cmd run lint`: نجح مع تحذيرين قديمين خارج نطاق قاعدة البيانات في ملفات student React.
- `npm.cmd run build`: نجح.

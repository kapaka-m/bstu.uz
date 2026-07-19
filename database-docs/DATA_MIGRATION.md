# BSTU International System — Database Content Migration Documentation

This document provides a comprehensive guide to the database content migration, detailing the extraction of website content and translations from static React files, mapping them to the MySQL schema, and seeding them dynamically.

---

## 1. Inspected and Migrated React Source Files

The maintained import utility parses historical React content captures from
`scripts/import-react-content/legacy-react-data/`, cleans ES module syntax,
evaluates them inside a Node.js VM context, and exports JSON datasets into
`apps/api/database/data/`. These files are not production runtime content and
are not imported by public React pages.

1. **`translations.js`** — UI translation dictionaries, structural text, department metadata translations, and green campus localizations.
2. **`programsData.js`** — Study programs metadata (degree, duration, coordinators, accreditations).
3. **`departmentsData.js`** — Academic departments, lab titles, and subject list curriculum.
4. **`universityData.js`** — Global university administration leadership (Rector, Vice-rectors, bios, contact details).
5. **`announcementsData.js`** — Campus notices, priority tags, and date boundaries.
6. **`videosData.js`** — Campus video tours, YouTube IDs, and details.
7. **`greenCampusData.js`** — Sustainability statistics and green campus achievement articles.
8. **`mockData.js`** — Services priorities data and additional blog elements.

`facultyTechnology.js` and `facultyTechnologyRequirements.cleaned.txt` are kept
only as historical source captures in the same legacy archive. Faculty and
department runtime pages fetch their content from Laravel API/MySQL.

---

## 2. Populated Database Tables

The extracted JSON datasets were mapped onto the normalized, multi-language MySQL schema:

- **`locales`** — Configured system language codes (`en`, `uz`, `ru`, `ar`) with text direction traits (`ltr` or `rtl`).
- **`translation_keys` & `translation_values`** — Flat-mapped UI labels grouped under system categories.
- **`pages` & `page_translations`** — Metadata and global details for `home` and `about` routes.
- **`page_blocks` & `page_block_translations`** — Dynamic homepage sections (`home_hero`, `home_rector`, `home_green_campus`).
- **`menus` & `menu_items` & `menu_item_translations`** — Dynamic navigation links.
- **`faculties` & `faculty_translations`** — Seeded the 4 core university faculties (`engineering`, `technology`, `service`, `natural`).
- **`departments` & `department_translations`** — Seeded all department subdivisions.
- **`programs` & `program_translations`** — Seeded all study programs with requirements, documents, and career paths.
- **`courses` & `course_translations`** — Dynamically mapped department subjects to courses and linked them to programs.
- **`news` & `news_translations`** — Dynamic News & Events articles with content fields managed through `/apanel/cms/news-events`.
- **`announcements` & `announcement_translations`** — Priority campus announcements.
- **`staff_profiles` & `staff_profile_translations`** — Leadership, faculty deans, and department instructors. Staff profiles include stable public slugs such as `rector`.
- **`services` & `service_translations`** — Core university services seeded by `ServiceSeeder`.
- **`videos` & `video_translations`** — Embedded YouTube promo configurations.
- **`green_campus_stats` & `green_campus_stat_translations`** — Sustainability metrics.
- **`green_campus_articles` & `green_campus_article_translations`** — Green Campus initiative articles.
- **`settings`** — Site contacts, emails, and social media profiles.

Generated `services.json` is retained as an import output for review/future migration while `ServiceSeeder` uses curated inline service rows. Blog content is managed only through the Blog CMS tables and `/apanel/cms/blog`.

---

## 3. Translation Generation and Key Grouping

### Generation Logic

1. Dynamic content models (News, Announcements, Programs, etc.) use a normalized translation sub-table (e.g. `news_translations`). They are seeded in a locale loop (`en`, `uz`, `ru`, `ar`).
2. If a translation is missing or blank for a non-English locale, the seeder automatically falls back to the English source text.
3. UI labels are flattened into dot-notation paths (e.g. `nav.home`) from `translations.json`.

### UI Key Groups

Translation keys are categorized under strictly whitelisted prefixes:

- `nav.*` — Navigation items
- `button.*` — Button labels
- `auth.*` — Login, registration, tokens
- `form.*` — Inputs, labels, placeholders
- `validation.*` — Error warnings
- `status.*` — Application state
- `apanel.*` — Admin links and headings
- `student.*` — Student portal UI
- `application.*` — Admission requests
- `notification.*` — System alerts
- `menu.*`, `error.*`, `success.*`, `page.*`, `section.*`, `footer.*`, `breadcrumb.*`

---

## 4. Media and Image Path Handling

- Image paths are stored as relative path strings (e.g. `assets/img/faculties/technology.jpg`).
- The REST API prefixes these values with the base URL automatically on retrieval via Eloquent casts or resource transformers.
- No huge media assets were duplicated. Existing public directory asset structures inside `apps/web/public/` remain compatible.

---

## 5. Rerunning Seeders

The seeders consume the JSON files in `apps/api/database/data/`. They do not call the React import script automatically.

Current JSON dataset ownership:

- `administration.json`: consumed by `StaffSeeder`.
- `announcements.json`: consumed by `AnnouncementSeeder`.
- `departments.json`: consumed by department, course, and staff seeding.
- `green_campus.json`: consumed by `GreenCampusSeeder`.
- `programs.json`: consumed by `ProgramSeeder`.
- `translations.json`: consumed by translation, menu, page, faculty, department, program, course, staff, announcement, video, and green campus seeders.
- `videos.json`: consumed by `VideoSeeder`.
- `services.json`: retained import output; current service records are curated inline in `ServiceSeeder`.

To clean the database, execute migrations, and re-populate the tables with migrated content, run:

```bash
cd apps/api
php -d extension=fileinfo -d extension=zip -d extension=pdo_mysql artisan migrate:fresh --seed
```

Use `migrate:fresh --seed` only for local reset or intentionally disposable
databases. For an existing database with real content, prefer normal additive
migrations and targeted seeders.

### Production/Shared Database Seeder Safety

Core system seeders are designed to be idempotent where they define stable
system records:

- Locales are keyed by `code`.
- Permissions and roles are keyed by `slug`.
- The default apanel user is created only when `apanel@bstu.uz` does not exist,
  so rerunning seeders does not reset a real administrator password.
- Green Campus stats are keyed by `sort_order`; Green Campus articles are keyed
  by `slug`.

Content seeders that intentionally manage public CMS records use stable slugs,
codes, or translation `(model_id, locale)` keys. On a shared database, run
targeted seeders only after reviewing whether the records are still
system-owned or have become admin-edited content.

Safe targeted examples:

```bash
cd apps/api
.\php-local.bat artisan db:seed --class=LocaleSeeder
.\php-local.bat artisan db:seed --class=PermissionSeeder
.\php-local.bat artisan db:seed --class=RoleSeeder
```

Avoid `migrate:fresh --seed` unless the database is disposable. Public CMS
tables are admin-editable after launch, so rerunning content seeders can
overwrite curated university content if ownership has changed.

---

## 6. React Content Import Utility

The maintained import helper lives at:

```text
scripts/import-react-content/import.js
```

Use it only when intentionally regenerating seed JSON from the legacy React
source captures. It reads content files from
`scripts/import-react-content/legacy-react-data/` and falls back to
`apps/web/src/data/translations.js` only for the technical translation fallback
file. It reads:

- `apps/web/src/data/translations.js` or `scripts/import-react-content/legacy-react-data/translations.js`
- `scripts/import-react-content/legacy-react-data/announcementsData.js`
- `scripts/import-react-content/legacy-react-data/programsData.js`
- `scripts/import-react-content/legacy-react-data/departmentsData.js`
- `scripts/import-react-content/legacy-react-data/universityData.js`
- `scripts/import-react-content/legacy-react-data/videosData.js`
- `scripts/import-react-content/legacy-react-data/greenCampusData.js`
- `scripts/import-react-content/legacy-react-data/mockData.js`

It writes:

- `apps/api/database/data/translations.json`
- `apps/api/database/data/announcements.json`
- `apps/api/database/data/programs.json`
- `apps/api/database/data/departments.json`
- `apps/api/database/data/administration.json`
- `apps/api/database/data/videos.json`
- `apps/api/database/data/green_campus.json`
- `apps/api/database/data/services.json`

Run it from the repository root:

```bash
node scripts/import-react-content/import.js
```

After running it, review the generated JSON diff before running seeders. The script is optional for normal database setup as long as the JSON files already exist.

### Seeder Consumption Notes

Current seeders consume these JSON files directly:

- `translations.json`: translations, menus, pages, page blocks, faculties, departments, programs, courses, announcements, staff, videos, green campus, and UI translation keys/values.
- `departments.json`: departments, courses, and staff metadata.
- `programs.json`: program metadata.
- `announcements.json`: announcement metadata.
- `administration.json`: leadership/staff metadata.
- `videos.json`: video metadata.
- `green_campus.json`: green campus stats and articles.

`services.json` is generated but not consumed by current seeders. Blog content is not generated from static JSON.

---

## 7. Managing Content Later via Apanel

All dynamic content and translations can be managed directly via the `/api/v1/apanel/` CRUD endpoints:

1. **Locales:** Toggle system active languages or order.
2. **Translation Values:** Directly edit UI text for any language.
3. **Dynamic Models:** Create, update, or delete pages, courses, programs, News & Events items, Blog posts, footer content, or green campus articles.

---

## 8. Content Excluded from Automatic Migration

- **Large Video Files:** Stored and served via external CDNs (YouTube).
- **PDF application files and personal documents:** Uploaded on demand by students in real-time, seeded as dynamic relations rather than static files.

## 9. Historical Source Cleanup

Raw source captures used during migration were merged into durable project data,
seed JSON, and active documentation. Remaining legacy captures are quarantined in
`scripts/import-react-content/legacy-react-data/` for historical re-import only.
Runtime public pages should depend on Laravel API/database content instead.

# BSTU International System — Database Schema Documentation

This document describes the complete MySQL database schema for Bukhara State Technical University's international portal.

---

## 1. Multilingual Translation & Localization

The database utilizes a hybrid approach for translations:

1. **Dynamic UI Translation System (Key-Value):** For menus, nav links, buttons, status badges, and forms.
2. **Entity-Specific Translation Tables:** For complex content models (Faculties, Departments, Programs, News, Page Blocks) where translations represent rich, structured columns rather than simple strings.

### Direction Support

Locales are configured in the `locales` table:

- English (`en`), Uzbek (`uz`), Russian (`ru`), and Arabic (`ar`) are seeded
  defaults.
- Additional locales can be created and activated from `/apanel/locales`.
- Each locale stores its own `direction` value, and the clients render RTL/LTR
  from the database response rather than from a fixed frontend list.

### Missing Translation Handling

The API handles translatable queries using the `HasTranslations` trait:

1. Search for the localized value in the requested locale.
2. If the translation record does not exist or the field is empty, fallback to the English (`en`) translation.
3. If English translation is also missing, return `null`.

Frontend code should not provide a hardcoded display string as a translation
fallback argument; missing translation keys should remain detectable through
raw keys, `null` values, or validation/reporting checks.

---

## 2. Table Structures by Category

### Authentication, Roles & Permissions

#### `users`

Base Laravel user accounts used by public auth, student accounts, and apanel users.

- `id` (BigInt, PK)
- `name` (Varchar)
- `email` (Varchar, Unique)
- `email_verified_at` (Timestamp, Nullable)
- `password` (Varchar)
- `remember_token` (Varchar, Nullable)
- `timestamps`

#### `roles`

- `id` (BigInt, PK)
- `name` (Varchar, Unique)
- `slug` (Varchar, Unique)
- `description` (Varchar, Nullable)
- `timestamps`

#### `permissions`

- `id` (BigInt, PK)
- `name` (Varchar, Unique)
- `slug` (Varchar, Unique)
- `description` (Varchar, Nullable)
- `timestamps`

#### `role_user` (Pivot)

- `user_id` (BigInt, FK to `users`, Cascade Delete)
- `role_id` (BigInt, FK to `roles`, Cascade Delete)
- *Constraints:* Composite primary key on `(user_id, role_id)`

#### `permission_role` (Pivot)

- `permission_id` (BigInt, FK to `permissions`, Cascade Delete)
- `role_id` (BigInt, FK to `roles`, Cascade Delete)
- *Constraints:* Composite primary key on `(permission_id, role_id)`

#### `personal_access_tokens`

Laravel Sanctum token table for API authentication.

- `id` (BigInt, PK)
- `tokenable_type` / `tokenable_id` (Morph columns)
- `name` (Text)
- `token` (Varchar, Unique)
- `abilities` (Text, Nullable)
- `last_used_at` (Timestamp, Nullable)
- `expires_at` (Timestamp, Nullable, Indexed)
- `timestamps`

---

### Localization Tables

#### `locales`

Stores active system languages and layouts.

- `id` (BigInt, PK, Auto Increment)
- `code` (Varchar, Unique, e.g. `'en'`, `'ar'`)
- `name` (Varchar, e.g. `'Arabic'`)
- `native_name` (Varchar, e.g. `'العربية'`)
- `direction` (Enum: `'ltr'`, `'rtl'`)
- `is_active` (TinyInt/Boolean, default `true`)
- `sort_order` (Int, default `0`)
- `timestamps`

#### `translation_keys`

Stores system translation key entries.

- `id` (BigInt, PK)
- `group` (Varchar, e.g. `'nav'`)
- `key` (Varchar, e.g. `'home'`)
- `description` (Text, Nullable)
- `is_system` (TinyInt/Boolean, default `false`)
- `timestamps`
- *Constraints:* Unique index on `(group, key)`

#### `translation_values`

Stores values associated with each locale.

- `id` (BigInt, PK)
- `translation_key_id` (BigInt, FK to `translation_keys`, Cascade Delete)
- `locale` (Varchar)
- `value` (Text)
- `timestamps`
- *Constraints:* Unique index on `(translation_key_id, locale)`

---

### Academic Structure

#### `faculties`

- `id` (BigInt, PK)
- `slug` (Varchar, Unique)
- `code` (Varchar, Unique, Nullable)
- `image` (Varchar, Nullable)
- `icon` (Varchar, Nullable)
- `sort_order` (Int, default `0`)
- `is_active` (TinyInt/Boolean, default `true`)
- `timestamps`

#### `faculty_translations`

- `id` (BigInt, PK)
- `faculty_id` (BigInt, FK to `faculties`, Cascade Delete)
- `locale` (Varchar)
- `name` (Varchar)
- `short_name` (Varchar, Nullable)
- `description` (Text, Nullable)
- `content_sections` (Json, Nullable) — optional structured rich content rendered by faculty detail pages.
- `meta_title` (Varchar, Nullable)
- `meta_description` (Text, Nullable)
- `timestamps`
- *Constraints:* Unique index on `(faculty_id, locale)`

#### `departments`

- `id` (BigInt, PK)
- `faculty_id` (BigInt, FK to `faculties`, Cascade Delete)
- `slug` (Varchar, Unique)
- `code` (Varchar, Unique, Nullable)
- `image` (Varchar, Nullable)
- `icon` (Varchar, Nullable)
- `head_name` (Varchar, Nullable)
- `email` (Varchar, Nullable)
- `phone` (Varchar, Nullable)
- `reception_time` (Varchar, Nullable)
- `source_url` (Varchar, Nullable)
- `sort_order` (Int, default `0`)
- `is_active` (TinyInt/Boolean, default `true`)
- `timestamps`

#### `department_translations`

- `id` (BigInt, PK)
- `department_id` (BigInt, FK to `departments`, Cascade Delete)
- `locale` (Varchar)
- `name` (Varchar)
- `short_name` (Varchar, Nullable)
- `description` (Text, Nullable)
- `content_sections` (Json, Nullable) — optional structured rich content rendered by department detail pages.
- `meta_title` (Varchar, Nullable)
- `meta_description` (Text, Nullable)
- `timestamps`
- *Constraints:* Unique index on `(department_id, locale)`

#### `programs`

- `id` (BigInt, PK)
- `faculty_id` (BigInt, FK to `faculties`, Cascade Delete)
- `department_id` (BigInt, FK to `departments`, Cascade Delete)
- `slug` (Varchar, Unique)
- `code` (Varchar, Unique, Nullable)
- `official_code` (Varchar, Nullable) — public academic code when the internal unique code must remain distinct.
- `track` (Varchar, Nullable) — specialization/track label for programs sharing the same official code.
- `degree` (Varchar)
- `duration_years` (Float)
- `study_mode` (Varchar)
- `language_of_study` (Varchar)
- `tuition_fee` (Decimal 10,2)
- `currency` (Varchar, default `'USD'`)
- `image` (Varchar, Nullable)
- `is_active` (TinyInt/Boolean, default `true`)
- `sort_order` (Int, default `0`)
- `timestamps`

#### `program_translations`

- `id` (BigInt, PK)
- `program_id` (BigInt, FK to `programs`, Cascade Delete)
- `locale` (Varchar)
- `name` (Varchar)
- `description` (Text, Nullable)
- `requirements` (Text, Nullable)
- `documents` (Text, Nullable)
- `curriculum_summary` (Text, Nullable)
- `career_opportunities` (Text, Nullable)
- `meta_title` (Varchar, Nullable)
- `meta_description` (Text, Nullable)
- `timestamps`
- *Constraints:* Unique index on `(program_id, locale)`

#### `courses`

- `id` (BigInt, PK)
- `code` (Varchar, Unique)
- `credits` (Int)
- `semester` (Int)
- `is_active` (TinyInt/Boolean, default `true`)
- `timestamps`

#### `course_translations`

- `id` (BigInt, PK)
- `course_id` (BigInt, FK to `courses`, Cascade Delete)
- `locale` (Varchar)
- `name` (Varchar)
- `description` (Text, Nullable)
- `timestamps`
- *Constraints:* Unique index on `(course_id, locale)`

#### `program_courses` (Pivot)

- `id` (BigInt, PK)
- `program_id` (BigInt, FK to `programs`, Cascade Delete)
- `course_id` (BigInt, FK to `courses`, Cascade Delete)
- `year` (Int)
- `semester` (Int)
- `is_required` (TinyInt/Boolean, default `true`)
- `timestamps`

---

### Public Content & Pages

#### `pages`

- `id` (BigInt, PK)
- `slug` (Varchar, Unique)
- `template` (Varchar, default `'default'`)
- `is_published` (TinyInt/Boolean, default `true`)
- `sort_order` (Int, default `0`)
- `timestamps`

#### `page_translations`

- `id` (BigInt, PK)
- `page_id` (BigInt, FK to `pages`, Cascade Delete)
- `locale` (Varchar)
- `title` (Varchar)
- `content` (LongText, Nullable)
- `meta_title` (Varchar, Nullable)
- `meta_description` (Text, Nullable)
- `timestamps`
- *Constraints:* Unique index on `(page_id, locale)`

#### `page_blocks`

- `id` (BigInt, PK)
- `page_id` (BigInt, FK to `pages`, Cascade Delete)
- `block_key` (Varchar)
- `type` (Varchar)
- `sort_order` (Int, default `0`)
- `settings_json` (Json, Nullable)
- `is_active` (TinyInt/Boolean, default `true`)
- `timestamps`

#### `page_block_translations`

- `id` (BigInt, PK)
- `page_block_id` (BigInt, FK to `page_blocks`, Cascade Delete)
- `locale` (Varchar)
- `title` (Varchar, Nullable)
- `subtitle` (Varchar, Nullable)
- `content` (Text, Nullable)
- `button_text` (Varchar, Nullable)
- `timestamps`
- *Constraints:* Unique index on `(page_block_id, locale)`

#### `menus`

- `id` (BigInt, PK)
- `key` (Varchar, Unique)
- `location` (Varchar)
- `is_active` (TinyInt/Boolean, default `true`)
- `timestamps`

#### `menu_items`

- `id` (BigInt, PK)
- `menu_id` (BigInt, FK to `menus`, Cascade Delete)
- `parent_id` (BigInt, FK to `menu_items`, Nullable, Cascade Delete)
- `route_name` (Varchar, Nullable)
- `url` (Varchar, Nullable)
- `icon` (Varchar, Nullable)
- `sort_order` (Int, default `0`)
- `is_active` (TinyInt/Boolean, default `true`)
- `timestamps`

#### `menu_item_translations`

- `id` (BigInt, PK)
- `menu_item_id` (BigInt, FK to `menu_items`, Cascade Delete)
- `locale` (Varchar)
- `label` (Varchar)
- `timestamps`
- *Constraints:* Unique index on `(menu_item_id, locale)`

---

### News & Announcements

#### `news`

- `id` (BigInt, PK)
- `slug` (Varchar, Unique)
- `image` (Varchar, Nullable)
- `category` (Varchar)
- `published_at` (Timestamp, Nullable)
- `is_published` (TinyInt/Boolean, default `true`)
- `views_count` (Int, default `0`)
- `author` (Varchar, Nullable)
- `author_image` (Varchar, Nullable)
- `comments_count` (Unsigned Int, default `0`)
- `timestamps`

#### `news_translations`

- `id` (BigInt, PK)
- `news_id` (BigInt, FK to `news`, Cascade Delete)
- `locale` (Varchar)
- `title` (Varchar)
- `summary` (Text, Nullable)
- `content` (LongText, Nullable)
- `meta_title` (Varchar, Nullable)
- `meta_description` (Text, Nullable)
- `timestamps`
- *Constraints:* Unique index on `(news_id, locale)`

#### `announcements`

- `id` (BigInt, PK)
- `slug` (Varchar, Unique)
- `type` (Varchar)
- `priority` (Varchar, default `'normal'`)
- `image` (Varchar, Nullable)
- `starts_at` (Timestamp, Nullable)
- `ends_at` (Timestamp, Nullable)
- `is_published` (TinyInt/Boolean, default `true`)
- `timestamps`

#### `announcement_translations`

- `id` (BigInt, PK)
- `announcement_id` (BigInt, FK to `announcements`, Cascade Delete)
- `locale` (Varchar)
- `title` (Varchar)
- `summary` (Text, Nullable)
- `content` (LongText, Nullable)
- `timestamps`
- *Constraints:* Unique index on `(announcement_id, locale)`

---

### Staff & Services

#### `staff_profiles`

- `id` (BigInt, PK)
- `slug` (Varchar, Unique, Nullable)
- `department_id` (BigInt, FK to `departments`, Nullable, Cascade Delete)
- `faculty_id` (BigInt, FK to `faculties`, Nullable, Cascade Delete)
- `photo` (Varchar, Nullable)
- `email` (Varchar, Nullable)
- `phone` (Varchar, Nullable)
- `sort_order` (Int, default `0`)
- `is_active` (TinyInt/Boolean, default `true`)
- `timestamps`

#### `staff_profile_translations`

- `id` (BigInt, PK)
- `staff_profile_id` (BigInt, FK to `staff_profiles`, Cascade Delete)
- `locale` (Varchar)
- `full_name` (Varchar)
- `position` (Varchar)
- `bio` (Text, Nullable)
- `office` (Varchar, Nullable)
- `timestamps`
- *Constraints:* Unique index on `(staff_profile_id, locale)`

#### `services`

- `id` (BigInt, PK)
- `slug` (Varchar, Unique)
- `icon` (Varchar, Nullable)
- `image` (Varchar, Nullable)
- `sort_order` (Int, default `0`)
- `is_active` (TinyInt/Boolean, default `true`)
- `timestamps`

#### `service_translations`

- `id` (BigInt, PK)
- `service_id` (BigInt, FK to `services`, Cascade Delete)
- `locale` (Varchar)
- `title` (Varchar)
- `description` (Text, Nullable)
- `content` (LongText, Nullable)
- `timestamps`
- *Constraints:* Unique index on `(service_id, locale)`

---

### Media & Global Settings

#### `media`

- `id` (BigInt, PK)
- `disk` (Varchar, default `'public'`)
- `path` (Varchar)
- `filename` (Varchar)
- `title` (Varchar, Nullable)
- `alt_text` (Text, Nullable)
- `type` (Varchar, Nullable)
- `mime_type` (Varchar)
- `size` (Unsigned BigInt)
- `is_public` (TinyInt/Boolean, default `true`)
- `alt_key` (Varchar, Nullable)
- `timestamps`

#### `videos`

- `id` (BigInt, PK)
- `slug` (Varchar, Unique)
- `url` (Varchar)
- `thumbnail` (Varchar, Nullable)
- `is_active` (TinyInt/Boolean, default `true`)
- `sort_order` (Int, default `0`)
- `timestamps`

#### `video_translations`

- `id` (BigInt, PK)
- `video_id` (BigInt, FK to `videos`, Cascade Delete)
- `locale` (Varchar)
- `title` (Varchar)
- `description` (Text, Nullable)
- `timestamps`
- *Constraints:* Unique index on `(video_id, locale)`

#### `settings`

- `id` (BigInt, PK)
- `key` (Varchar, Unique)
- `value` (Text, Nullable)
- `type` (Varchar, default `'text'`)
- `group` (Varchar, default `'general'`)
- `is_public` (TinyInt/Boolean, default `true`)
- `timestamps`

---

### Green Campus

#### `green_campus_stats`

- `id` (BigInt, PK)
- `icon` (Varchar, Nullable)
- `sort_order` (Int, default `0`)
- `timestamps`

#### `green_campus_stat_translations`

- `id` (BigInt, PK)
- `green_campus_stat_id` (BigInt, FK to `green_campus_stats`, Cascade Delete)
- `locale` (Varchar)
- `value` (Varchar)
- `label` (Varchar)
- `timestamps`
- *Constraints:* Unique index on `(green_campus_stat_id, locale)`

#### `green_campus_articles`

- `id` (BigInt, PK)
- `slug` (Varchar, Unique)
- `image` (Varchar, Nullable)
- `gallery` (Json, Nullable)
- `views` (Int, default `0`)
- `timestamps`

#### `green_campus_article_translations`

- `id` (BigInt, PK)
- `green_campus_article_id` (BigInt, FK to `green_campus_articles`, Cascade Delete)
- `locale` (Varchar)
- `title` (Varchar)
- `category` (Varchar, Nullable)
- `excerpt` (Text, Nullable)
- `content` (LongText, Nullable)
- `author` (Varchar, Nullable)
- `timestamps`
- *Constraints:* Unique index on `(green_campus_article_id, locale)`

---

### Student Application & Lifecycle System

#### `student_profiles`

Stores additional data for authenticated users applying as international students.

- `id` (BigInt, PK)
- `user_id` (BigInt, FK to `users`, Cascade Delete)
- `phone` (Varchar)
- `gender` (Varchar)
- `birth_date` (Date)
- `passport_number` (Varchar)
- `passport_expiry_date` (Date, Nullable)
- `nationality` (Varchar)
- `address` (Text)
- `timestamps`

#### `guardians`

Emergency contact and billing guardian profiles.

- `id` (BigInt, PK)
- `student_profile_id` (BigInt, FK to `student_profiles`, Cascade Delete)
- `name` (Varchar)
- `relation` (Varchar)
- `phone` (Varchar)
- `email` (Varchar, Nullable)
- `timestamps`

#### `education_backgrounds`

Prior academic qualifications and metrics.

- `id` (BigInt, PK)
- `student_profile_id` (BigInt, FK to `student_profiles`, Cascade Delete)
- `institution_name` (Varchar)
- `degree_obtained` (Varchar)
- `gpa` (Varchar)
- `graduation_year` (Int)
- `timestamps`

#### `applications`

University admission applications.

- `id` (BigInt, PK)
- `student_profile_id` (BigInt, FK to `student_profiles`, Cascade Delete)
- `program_id` (BigInt, FK to `programs`, Cascade Delete)
- `faculty_id` (BigInt, FK to `faculties`, Nullable, Null On Delete)
- `department_id` (BigInt, FK to `departments`, Nullable, Null On Delete)
- `degree_level` (Varchar, Nullable)
- `language_of_study` (Varchar, Nullable)
- `study_mode` (Varchar, Nullable)
- `status` (Varchar, default `'pending'`)
- `timestamps`

#### `application_status_histories`

Application status change logs.

- `id` (BigInt, PK)
- `application_id` (BigInt, FK to `applications`, Cascade Delete)
- `old_status` (Varchar, Nullable)
- `new_status` (Varchar, Nullable)
- `status` (Varchar)
- `comment` (Text, Nullable)
- `note` (Text, Nullable)
- `changed_by` (BigInt, FK to `users`, Cascade Delete)
- `timestamps`

#### `application_documents`

Digital document uploads.

- `id` (BigInt, PK)
- `application_id` (BigInt, FK to `applications`, Cascade Delete)
- `document_name` (Varchar)
- `document_type` (Varchar, Nullable)
- `file_path` (Varchar)
- `original_name` (Varchar, Nullable)
- `mime_type` (Varchar, Nullable)
- `size` (Unsigned BigInt, Nullable)
- `status` (Varchar, default `'pending'`)
- `note` (Text, Nullable)
- `timestamps`

#### `contracts`

Tuition contracts.

- `id` (BigInt, PK)
- `application_id` (BigInt, FK to `applications`, Cascade Delete)
- `contract_number` (Varchar, Unique)
- `amount` (Decimal 10,2)
- `status` (Varchar, default `'pending'`)
- `timestamps`

#### `payments`

Tuition fees transaction logs.

- `id` (BigInt, PK)
- `contract_id` (BigInt, FK to `contracts`, Cascade Delete)
- `payment_number` (Varchar, Unique)
- `amount` (Decimal 10,2)
- `payment_date` (Timestamp)
- `status` (Varchar, default `'pending'`)
- `timestamps`

#### `enrollments`

Final matriculated student allocations.

- `id` (BigInt, PK)
- `student_profile_id` (BigInt, FK to `student_profiles`, Cascade Delete)
- `program_id` (BigInt, FK to `programs`, Cascade Delete)
- `student_number` (Varchar, Unique)
- `academic_year` (Varchar)
- `status` (Varchar, default `'active'`)
- `timestamps`

#### `document_requests`

Official requests (e.g. visa support, academic transcripts, reference letters).

- `id` (BigInt, PK)
- `student_profile_id` (BigInt, FK to `student_profiles`, Cascade Delete)
- `request_type` (Varchar)
- `status` (Varchar, default `'pending'`)
- `comment` (Text, Nullable)
- `timestamps`

#### `notifications`

System notifications for users.

- `id` (BigInt, PK)
- `user_id` (BigInt, FK to `users`, Cascade Delete)
- `title` (Varchar)
- `message` (Text)
- `is_read` (TinyInt/Boolean, default `false`)
- `timestamps`

---

### Communication

#### `inquiries`

Public anonymous contact requests.

- `id` (BigInt, PK)
- `name` (Varchar)
- `email` (Varchar)
- `subject` (Varchar)
- `message` (Text)
- `status` (Varchar, default `'pending'`)
- `timestamps`

#### `support_tickets`

Student support ticketing systems.

- `id` (BigInt, PK)
- `user_id` (BigInt, FK to `users`, Cascade Delete)
- `subject` (Varchar)
- `status` (Varchar, default `'open'`)
- `priority` (Varchar, default `'normal'`)
- `timestamps`

#### `support_ticket_messages`

Support ticket message logs.

- `id` (BigInt, PK)
- `support_ticket_id` (BigInt, FK to `support_tickets`, Cascade Delete)
- `user_id` (BigInt, FK to `users`, Cascade Delete)
- `message` (Text)
- `timestamps`

#### `comments`

Polymorphic comments linked to news, pages, or student applications.

- `id` (BigInt, PK)
- `user_id` (BigInt, FK to `users`, Nullable, Null On Delete) — nullable for anonymous public comments.
- `commentable_type` (Varchar, e.g. `'App\Models\News'`)
- `commentable_id` (BigInt)
- `content` (Text)
- `timestamps`
- *Index:* index on `(commentable_type, commentable_id)`

---

### Auditing & System Security

#### `audit_logs`

Activity log detailing CRUD actions.

- `id` (BigInt, PK)
- `user_id` (BigInt, FK to `users`, Set Null, Nullable)
- `action` (Varchar, e.g. `'create'`, `'update'`)
- `model_type` (Varchar, Nullable)
- `model_id` (BigInt, Nullable)
- `old_values` (Json, Nullable)
- `new_values` (Json, Nullable)
- `ip_address` (Varchar 45, Nullable)
- `user_agent` (Text, Nullable)
- `created_at` (Timestamp, defaults to `CURRENT_TIMESTAMP`)

---

## 3. Performance Indexes

High-read public CMS and student/apanel workflows use additional composite indexes added by
`2026_07_15_000001_add_performance_indexes.php`.

Key index groups:

- Public content ordering/filtering: `faculties(is_active, sort_order)`, `departments(faculty_id, is_active, sort_order)`, `programs(faculty_id, is_active, sort_order)`, `programs(department_id, is_active, sort_order)`, `pages(is_published, sort_order)`, `page_blocks(page_id, is_active, sort_order)`.
- News and announcements: `news(is_published, published_at)`, `news(category, is_published, published_at)`, `announcements(is_published, priority, created_at)`, `announcements(is_published, ends_at)`.
- Menus/settings/media: `menus(location, is_active)`, `menu_items(menu_id, parent_id, is_active, sort_order)`, `settings(is_public, group)`, `media(is_public, type)`.
- Student workflows: `applications(student_profile_id, status, created_at)`, `application_documents(application_id, document_type, status)`, `contracts(application_id, status)`, `payments(contract_id, status, payment_date)`, `notifications(user_id, is_read, created_at)`, `support_tickets(user_id, status, created_at)`.
- Auditing: `audit_logs(user_id, created_at)` and `audit_logs(model_type, model_id)`.

Unique indexes remain the source of truth for slugs and translation rows, including `(translation_key_id, locale)` and each entity translation table's `(entity_id, locale)` pair.

---

## 4. Administrator Role & Permissions

The main full-control administrator role is named `apanel`. The default user is `apanel@bstu.uz` (password: `password`), who is associated with this role.

### Permissions Assigned to `apanel`

1. `manage-locales`
2. `manage-translations`
3. `manage-settings`
4. `manage-menus`
5. `manage-pages`
6. `manage-faculties`
7. `manage-departments`
8. `manage-programs`
9. `manage-courses`
10. `manage-news`
11. `manage-announcements`
12. `manage-staff`
13. `manage-services`
14. `manage-media`
15. `manage-users`
16. `manage-roles`
17. `manage-permissions`
18. `manage-students`
19. `manage-applications`
20. `manage-documents`
21. `manage-contracts`
22. `manage-payments`
23. `manage-inquiries`
24. `manage-support-tickets`
25. `manage-comments`
26. `manage-notifications`
27. `view-audit-logs`

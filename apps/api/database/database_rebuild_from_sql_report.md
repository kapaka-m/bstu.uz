# Database Rebuild From SQL Report

## 1. SQL dump used as source of truth

- Main source: `database/Mer/bstu_international_complete_merged.sql`
- SHA-256 used by generated metadata: `b013e2a67b1f0c84bb1a4b75cebf8af0498a0ef4136f5902fb63ac15085115c9`
- Comparison-only files reviewed:
  - `database/Mer/bstu_international.sql`
  - `database/Mer/database_merge_report_complete.md`

## 2. Current database files reviewed

- Existing migrations in `database/migrations`
- Existing seeders in `database/seeders`
- Existing data files in `database/data`
- Merge report in `database/Mer/database_merge_report_complete.md`

## 3. Tables found in SQL

The complete dump contains 110 tables:

`about_page_content_entries`, `about_page_content_entry_translations`, `about_page_translations`, `about_pages`, `administration_profile_translations`, `administration_profiles`, `administration_setting_translations`, `administration_settings`, `admissions`, `announcement_setting_translations`, `announcement_settings`, `announcement_translations`, `announcements`, `application_countries`, `application_documents`, `application_equivalencies`, `application_fee_payments`, `application_nationalities`, `application_status_histories`, `applications`, `audit_logs`, `blog_comments`, `blog_setting_translations`, `blog_settings`, `blog_translations`, `blogs`, `cache`, `cache_locks`, `comments`, `contact_page_translations`, `contact_pages`, `contracts`, `course_translations`, `courses`, `department_translations`, `departments`, `document_requests`, `document_requirements`, `education_backgrounds`, `enrollments`, `equivalency_courses`, `faculties`, `faculty_translations`, `failed_jobs`, `green_campus_article_translations`, `green_campus_articles`, `green_campus_setting_translations`, `green_campus_settings`, `green_campus_stat_translations`, `green_campus_stats`, `guardians`, `housing_requests`, `inquiries`, `interactive_service_setting_translations`, `interactive_service_settings`, `job_batches`, `jobs`, `locales`, `media`, `menu_item_translations`, `menu_items`, `menus`, `migrations`, `news`, `news_event_setting_translations`, `news_event_settings`, `news_translations`, `newsletter_subscriptions`, `notifications`, `page_block_translations`, `page_blocks`, `page_translations`, `pages`, `password_reset_tokens`, `payments`, `permission_role`, `permissions`, `personal_access_tokens`, `prikazes`, `program_courses`, `program_translations`, `programs`, `residence_permit_processes`, `role_user`, `roles`, `service_fee_payments`, `service_translations`, `services`, `sessions`, `settings`, `staff_profile_translations`, `staff_profiles`, `student_profiles`, `student_visa_processes`, `support_ticket_messages`, `support_tickets`, `translation_keys`, `translation_values`, `university_center_setting_translations`, `university_center_settings`, `university_center_translations`, `university_centers`, `users`, `video_comments`, `video_gallery_setting_translations`, `video_gallery_settings`, `video_translations`, `videos`, `web_footer_translations`, `web_footers`.

## 4. Tables missing from migrations and fixed

The old migration set represented an incremental/partial project history and did not reliably reproduce the latest merged dump. It was replaced as the schema source of truth by:

- `database/migrations/0000_01_01_000000_create_full_database_snapshot_schema.php`
- `database/data/full_database_snapshot/schema.sql`

The previous migration files remain in place but are explicit no-op stubs so Laravel migration bookkeeping is stable while schema creation comes from the complete dump.

## 5. Columns missing from migrations and fixed

All column definitions now come directly from `schema.sql`, generated from `bstu_international_complete_merged.sql`. Verification found no column differences between the dump-derived source schema and `migrate:fresh` output for the 109 application tables.

## 6. Indexes/FKs missing and fixed

All dump `ALTER TABLE` index, unique, primary key, auto-increment, and foreign key statements are included in `schema.sql`. Verification found no index or foreign key differences for the 109 application tables.

## 7. Seed data files created/updated

Created:

- `database/data/full_database_snapshot/schema.sql`
- `database/data/full_database_snapshot/data.sql`
- `database/data/full_database_snapshot/metadata.json`
- `database/data/full_database_snapshot/verification_result.json`

Large row data is stored in `data.sql`, not hard-coded into PHP arrays. Insert batches are chunked to avoid MariaDB `max_allowed_packet` failures.

## 8. Seeders created/updated

Created:

- `database/seeders/FullDatabaseSnapshotSeeder.php`

Updated:

- `database/seeders/DatabaseSeeder.php`

`FullDatabaseSnapshotSeeder` disables FK checks, truncates dump tables except Laravel's `migrations` repository table, imports `data.sql`, then reapplies dump auto-increment values. Re-running `php artisan db:seed` is idempotent because rows are rebuilt from the snapshot rather than appended.

## 9. DatabaseSeeder changes

`DatabaseSeeder` now calls only `FullDatabaseSnapshotSeeder::class`. Older partial seeders remain available but are no longer called by the default seed path.

## 10. Tables fully reproducible after migrate:fresh --seed

All 109 application tables from the dump are fully reproducible with matching schema and row counts. The target database contains 110 tables after `migrate:fresh`; the extra handled table is Laravel's `migrations` table, which exists but is populated by Artisan migration bookkeeping rather than dump seed data.

## 11. Any table not reproduced exactly and why

- `migrations`: not seeded from the dump. Laravel creates and maintains this table during `artisan migrate`. Seeding historical migration rows from the dump would corrupt Laravel's understanding of the current consolidated migration files. The table exists after `migrate:fresh`.

No application table is excluded.

## 12. Verification commands run

Temporary databases only:

```bash
php -r '$pdo=new PDO("mysql:host=127.0.0.1;port=3306;charset=utf8mb4","root",""); $pdo->exec("CREATE DATABASE IF NOT EXISTS `bstu_rebuild_source_tmp` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"); $pdo->exec("CREATE DATABASE IF NOT EXISTS `bstu_rebuild_target_tmp` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");'
$env:DB_DATABASE='bstu_rebuild_target_tmp'; php artisan migrate:fresh --force
$env:DB_DATABASE='bstu_rebuild_target_tmp'; php artisan migrate:fresh --seed --force
$env:DB_DATABASE='bstu_rebuild_target_tmp'; php artisan db:seed --force
php database/tools/verify_full_database_snapshot.php bstu_rebuild_source_tmp bstu_rebuild_target_tmp
```

## 13. Final schema comparison result

- Compared application tables: 109
- Target table count after `migrate:fresh`: 110
- Tables match: yes
- Columns match: yes
- Indexes match: yes
- Foreign keys match: yes

## 14. Final row count comparison result

- Compared application rows from dump-derived source: 22,211
- Rebuilt application rows after `migrate:fresh --seed`: 22,211
- Row counts match per table: yes
- Orphan foreign keys: 0
- Duplicate unique keys: 0
- Second `php artisan db:seed --force`: passed with no duplicate row, unique key, or FK errors

The merge report's 22,271 total rows includes 60 rows in Laravel's historical `migrations` table. Those rows are intentionally not seeded; Artisan records 61 current migration rows after the consolidated migration plus no-op historical migration files run.

## 15. Remaining issues

No remaining application schema or data mismatch was found. The only intentional difference is Laravel-owned `migrations` table row content.

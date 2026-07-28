# CMS Modules & Page Migration Specifications

This document outlines the operational specs for each dynamic module migrated from static code to database-backed APIs.

## 1. News & Events

- **Frontend Page**: `/news` lists all active news items. `/news/:slug` shows detailed news content.
- **Apanel Route**: `/apanel/cms/news-events`.
- **Database Tables**: `news`, `news_translations`, `news_event_settings`.

## 2. Interactive Services

- **Frontend Page**: `/services` lists all academic and interactive student tools.
- **Apanel Route**: `/apanel/cms/interactive-services`.
- **Database Tables**: `services`, `service_translations`.

## 3. Sustainability / Green Campus

- **Frontend Page**: `/green-campus` and homepage section.
- **Apanel Route**: `/apanel/cms/green-campus`.
- **Database Tables**: `green_campus_articles`, `green_campus_stats`, `green_campus_settings`.

## 4. Blog & Articles

- **Frontend Page**: `/blog` and details page. Includes a dynamic comments section.
- **Apanel Route**: `/apanel/cms/blog`.
- **Database Tables**: `blogs`, `blog_translations`, `blog_comments`.

## 5. Video Gallery (Video BDTU)

- **Frontend Page**: Video gallery section with runtime counters and play statistics.
- **Apanel Route**: `/apanel/cms/video-bdtu`.
- **Database Tables**: `videos`, `video_translations`, `video_comments`.

## 6. Header Navbar Controls

- **Behavior**: Allows drag-and-drop sorting (Up/Down) and active toggles for main menus.
- **Apanel Route**: `/apanel/cms/header-navbar`.
- **Database Tables**: `menus`, `menu_items`, `menu_item_translations`.

## 7. Contact Page & Inquiries

- **Behavior**: Renders contact details dynamically and captures user inquiries.
- **Apanel Route**: `/apanel/cms/contact-page` & `/apanel/management/contact`.
- **Database Tables**: `contact_pages`, `contact_page_translations`, `inquiries`.

## 8. Student Portal & Online Admissions

- **Frontend Route**: `/apply` and `/student/dashboard`.
- **Behavior**: Multi-phase admission workflow covering nationality, country selection, academic credentials, and document uploads.

## 9. Legacy Draft Cleanup

Historical root-level drafts and exports for academic structures were reviewed
and removed during documentation cleanup. Their useful summary is retained in
the active documentation set:

- Faculty migration status and department slugs are documented in
  `docs/04_faculty_migration_notes.md`.
- Administrative departments and centers are summarized in
  `docs/03_university_departments_and_centers.md`.
- Database migration ownership and seed data guidance are documented in
  `database-docs/database_migration_guide.md`.

Do not recreate root-level draft/export files for CMS content. Long-lived notes
belong in `docs/`, `database-docs/`, or `storage-docs/`.

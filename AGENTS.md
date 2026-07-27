# BSTU International Website — Coding Agent Guide

This repository is an existing monorepo for the BSTU International system. Do not restart it, recreate Laravel, move `apps/web`, create a new React app, or recreate Flutter.

## Project Layout

- `apps/web` — React 19 + Vite + Tailwind CSS 4 public website, student portal, and `apanel`.
- `apps/api` — Laravel 13 REST API using MySQL and Laravel Sanctum.
- `apps/mobile` — Flutter mobile app using the same `/api/v1` backend.
- `docs` — architecture, API, backend, frontend integration, apanel, student system, and mobile documentation.
- `database-docs` — schema and data migration documentation.
- `storage-docs` — Laravel storage and file URL guidance.

## Common Commands

Web:

```bash
cd apps/web
npm install
npm.cmd run lint
npm.cmd run build
npm.cmd run dev
```

API:

```bash
cd apps/api
composer install
.\php-local.bat artisan route:list
.\php-local.bat artisan migrate:fresh --seed
```

Mobile:

```bash
cd apps/mobile
flutter pub get
flutter analyze
flutter run
```

If `flutter` is not available on PATH, report that clearly rather than claiming mobile analysis passed.

## Architecture Rules

- Keep the React frontend in `apps/web`.
- Keep Laravel API code in `apps/api`.
- Keep Flutter code in `apps/mobile`.
- Use the existing REST API and MySQL-backed content system where possible.
- Preserve public website, apanel, student system, and Laravel API routes.
- Do not add absolute Windows paths to frontend source or bundles.
- Do not render raw source, audit, or debug labels publicly.

## Frontend Conventions

- Pages are lazy-loaded in `apps/web/src/App.jsx`.
- Shared public layout uses `Header`, `Footer`, and `ScrollToTop`.
- Use `useLanguage()` / `useLocale()` for visible labels and locale switching.
- Arabic uses RTL through `LocaleContext`, which sets document `lang` and `dir`.
- Use Tailwind responsive classes and existing visual patterns.
- Use Lucide React icons where icons are needed.
- Add `alt` text to images and fallback behavior when assets may be missing.
- External links should use `target="_blank"` with `rel="noopener noreferrer"`.

## Backend Conventions

- API routes live in `apps/api/routes/api.php`.
- Public APIs are handled primarily by `Api\PublicApiController`.
- Student workflows are handled by `Api\StudentApiController`.
- Apanel CRUD is handled by `Api\AdminCrudController`.
- Database content is seeded from `apps/api/database/data/` and seeder classes.
- Public content should be treated as MySQL/API source-of-truth. Static React/Dart data files are migration references or temporary fallbacks unless a feature explicitly documents otherwise.
- Legacy React import scripts were removed after their reviewed content was merged into `apps/api/database/data/`. Do not use `apps/web/src/data` for production content. It contains only the technical translation fallback.
- Keep file uploads on Laravel's public disk and document URL handling in `storage-docs`.
- Preserve `apps/api/public/storage`; it is a Laravel public link/junction to `storage/app/public`, not a duplicate upload folder.
- Database changes in `apps/api` should use additive migrations. Do not run `migrate:fresh` against shared or real data, and keep seeders idempotent using stable keys such as slugs, codes, locales, and emails.
- Keep API production settings explicit: `APP_DEBUG=false`, narrow `CORS_ALLOWED_ORIGINS`, configured Sanctum domains/token expiration, rotating logs, and a real cache/queue store for production.
- Do not cache private student or apanel responses. Public CMS cache is versioned by `public_content_cache_version` and can be reset with `cache:clear` / `optimize:clear`.
- Do not manually edit `package-lock.json` or `node_modules/.package-lock.json`; npm lockfile version `3` is expected for this project.

## Documentation

Keep durable documentation in:

- `README.md`
- `docs/`
- `database-docs/`
- `storage-docs/`

Temporary agent/task folders such as `.agents/` and `.kilo/` are ignored and should not be used as project source. Historical source captures should be merged into durable data files, seed JSON, or active documentation rather than kept as runtime dependencies.

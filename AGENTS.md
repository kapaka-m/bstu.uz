# BSTU International Website — Coding Agent Guide

This repository is an existing monorepo for the BSTU International system. Do not restart it, recreate Laravel, move `apps/web`, or create a new React app.

## Project Layout

- `apps/web` — React 19 + Vite + Tailwind CSS 4 public website, student portal, and `apanel`.
- `apps/api` — Laravel 13 REST API using MySQL and Laravel Sanctum.

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
.\php-local.bat artisan migrate
.\php-local.bat artisan db:seed
```

## Architecture Rules

- Keep the React frontend in `apps/web`.
- Keep Laravel API code in `apps/api`.
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
- Public content should be treated as MySQL/API source-of-truth. Static React/Dart data files are migration references only and must not be used as runtime fallbacks.
- Legacy React import scripts were removed after their reviewed content was merged into `apps/api/database/data/`. Do not use `apps/web/src/data` for production content or translation fallbacks.
- Keep file uploads on Laravel's public disk and use the shared storage/media helpers for URL handling.
- Preserve `apps/api/public/storage`; it is a Laravel public link/junction to `storage/app/public`, not a duplicate upload folder.
- Database changes in `apps/api` should use additive migrations. Do not run `migrate:fresh` against shared or real data, and keep seeders idempotent using stable keys such as slugs, codes, locales, and emails. `migrate:fresh --seed` is only for disposable local databases.
- Keep API production settings explicit: `APP_DEBUG=false`, narrow `CORS_ALLOWED_ORIGINS`, configured Sanctum domains/token expiration, rotating logs, and a real cache/queue store for production.
- Do not cache private student or apanel responses. Public CMS cache is versioned by `public_content_cache_version` and can be reset with `cache:clear` / `optimize:clear`.
- Do not manually edit `package-lock.json` or `node_modules/.package-lock.json`; npm lockfile version `3` is expected for this project.

## Documentation

Keep durable project guidance in `README.md`, `AGENTS.md`, active source comments where needed, or seed/data files. Temporary agent/task folders such as `.agents/` and `.kilo/` are ignored and should not be used as project source. Historical source captures should be merged into durable data files or seed JSON rather than kept as runtime dependencies.

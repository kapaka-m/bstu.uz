# BSTU API Audit

This file documents the current `apps/api` Laravel API structure, validation status, and the rules used to keep public production content out of runtime PHP code.

## Purpose

`apps/api` is the Laravel 13 REST API for the BSTU International public website, student portal, and `apanel`. The API owns the MySQL-backed CMS and exposes public, student, and admin endpoints under `api/v1`.

## Runtime Content Policy

Public website content must come from database models and CMS tables, not hard-coded controller arrays or PHP fallbacks.

Allowed static source:

- Route definitions.
- Validation rules.
- Model fillable/cast/relationship definitions.
- CRUD resource schemas used by admin endpoints.
- HTTP status messages and API error keys.
- Workflow status keys.
- Migration schema definitions.
- Empty defaults for optional CMS settings.
- PDF layout labels required by generated documents.

Not allowed static source:

- Public page copy embedded directly in controllers.
- Faculty, department, program, staff, news, blog, announcement, video, contact, footer, or home content returned from PHP arrays.
- Runtime fallback content copied from frontend, old React files, Dart files, or temporary agent output.
- Public debug labels, raw source labels, or audit labels exposed as user-facing content.

## Database Seed Data

The files under `database/data/schema_groups` and `database/data/seed_groups` are controlled database snapshots. They are used by migrations and seeders to rebuild a disposable/local database.

These files contain CMS content by design, but they are not runtime fallback content. At runtime, public API responses are loaded from MySQL through Eloquent models and controllers.

Do not remove `database/data` unless the project gets a replacement database export and seeding strategy.

## Main API Areas

- Public APIs: `app/Http/Controllers/Api/PublicApiController.php`
- Home CMS APIs: `app/Http/Controllers/Api/HomeCmsController.php`
- Student APIs: `app/Http/Controllers/Api/StudentApiController.php`
- Student application portal APIs: `app/Http/Controllers/Api/StudentApplicationPortalController.php`
- Admin CRUD APIs: `app/Http/Controllers/Api/AdminCrudController.php`
- Admin application workflow APIs: `app/Http/Controllers/Api/ApanelApplicationWorkflowController.php`
- Auth APIs: `app/Http/Controllers/Api/V1/AuthController.php`
- Auth CMS APIs: `app/Http/Controllers/Api/AuthCmsController.php`

Routes are defined in `routes/api.php` and `routes/web.php`.

## Storage And Media

Uploaded media and generated student documents must stay on Laravel storage disks. Public files should be served through the configured public disk and `public/storage` link.

Do not hard-code absolute Windows paths in API responses or stored public content.

## Validation

The following checks were run from `apps/api`:

```bash
composer validate --strict
.\vendor\bin\pint --test
.\php-local.bat artisan test
.\php-local.bat artisan route:list
```

PHP syntax was also checked for all PHP files under `app`, `routes`, `config`, `database`, `tests`, and `resources`.

## Maintenance Rules

Keep public content in the database/CMS.

Keep seed snapshots in `database/data` and load them through seeders.

Use additive migrations for database changes.

Do not run `migrate:fresh` against shared or real data.

Keep API production settings explicit: `APP_DEBUG=false`, narrow CORS origins, configured Sanctum domains, configured token expiration, rotating logs, and real cache/queue stores.

Run validation before handing off changes:

```bash
composer validate --strict
.\vendor\bin\pint --test
.\php-local.bat artisan test
```

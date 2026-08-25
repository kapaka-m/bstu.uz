# Bukhara State Technical University (BSTU) International Website

Official monorepo for the **BSTU International Website**, including:

* Public international website
* Student portal
* Administrative panel (`apanel`)
* Laravel REST API
* MySQL-backed CMS
* Authentication and authorization
* Localization / RTL-LTR infrastructure
* Media and storage management
* Database-backed public content

The repository is an existing production-oriented system.

Do not recreate the frontend or backend architecture unnecessarily.

---

## 1. Repository Structure

```text
international.bstu.uz/
│
├── apps/
│   ├── web/                         # React frontend
│   └── api/                         # Laravel REST API
│
├── AGENTS.md                        # Repository-wide agent instructions
├── SKILL.md                         # Page audit & production-readiness protocol
├── README.md                        # Developer/project documentation
├── international.bstu.uz.code-workspace
├── .gitignore
└── ...
```

---

## 2. Applications

## Frontend Technology

```text
apps/web
```

React application containing:

* Public BSTU International website
* Student portal
* `apanel` administrative interface
* Frontend routing
* Shared layouts
* Shared components
* Public pages
* Student pages
* Admin/CMS pages
* API clients/services
* Localization infrastructure
* Browser UI and responsive layouts

---

## Backend Technology

```text
apps/api
```

Laravel application containing:

* REST API
* Public APIs
* Student APIs
* Admin/CMS APIs
* Authentication
* Authorization
* Validation
* Database models
* Migrations
* Seeders
* CMS content management
* Translation infrastructure
* Media/storage handling
* Caching
* Application configuration

---

## 3. Technology Stack

## Frontend

* React 19
* React Router 7
* Vite
* Tailwind CSS 4
* Framer Motion
* Swiper
* Lucide React
* React Context

---

## Backend

* Laravel 13
* PHP 8.3+
* Laravel Sanctum
* REST API

---

## Database

* MySQL
* InnoDB
* Eloquent ORM

---

## Localization

Default seeded locales:

```text
en
uz
ru
ar
```

Typical configuration:

| Locale | Language | Direction |
| ------ | -------- | --------- |
| `en`   | English  | LTR       |
| `uz`   | Uzbek    | LTR       |
| `ru`   | Russian  | LTR       |
| `ar`   | Arabic   | RTL       |

The database is the authoritative source for active locale configuration.

---

## 4. Architecture Overview

The application generally follows:

```text
React Frontend
      ↓
REST API
      ↓
Laravel
      ↓
Eloquent Models
      ↓
MySQL
```

Administratively managed public content generally follows:

```text
Admin / CMS
      ↓
Laravel Admin API
      ↓
MySQL
      ↓
Public API
      ↓
React Website
```

The database/API architecture is the runtime source of truth for managed public content.

Do not move database-managed production content into static React files as a hidden runtime fallback.

---

## 5. Repository Documentation

The repository intentionally separates developer documentation, agent rules, and audit procedures.

## README.md Responsibilities

```text
README.md
```

Purpose:

* project overview
* setup instructions
* architecture documentation
* development workflow
* production/deployment basics

---

## AGENTS.md Responsibilities

```text
AGENTS.md
```

Purpose:

* repository-wide coding-agent instructions
* architecture constraints
* frontend/backend conventions
* database safety
* security rules
* Git/change safety
* coding-agent behavior

Coding agents operating inside this repository should read and obey `AGENTS.md`.

---

## SKILL.md Responsibilities

```text
SKILL.md
```

Purpose:

* page audits
* route audits
* route-family audits
* end-to-end verification
* browser/runtime testing
* Admin/CMS verification
* database verification
* repair workflow
* production-readiness decisions

For a page audit, an agent can normally be instructed with:

```text
Audit:
http://localhost:5173/about
```

For a route family:

```text
Audit:
http://localhost:5173/announcements/*
```

For list + detail routes:

```text
Audit these as one connected feature:

http://localhost:5173/announcements
http://localhost:5173/announcements/*
```

`AGENTS.md` instructs the coding agent to load and follow `SKILL.md` automatically for audit tasks.

---

## 6. Local Development Requirements

Recommended local development environment:

* Windows
* Git
* Node.js
* npm
* PHP 8.3+
* Composer
* MySQL

The Laravel backend requires appropriate PHP extensions.

Common required extensions include:

```text
pdo_mysql
fileinfo
zip
```

Additional extensions may be required by installed Composer packages.

---

## 7. Local Development URLs

Typical frontend URL:

```text
http://localhost:5173
```

Typical backend URL:

```text
http://127.0.0.1:8000
```

Typical phpMyAdmin URL when installed locally:

```text
http://localhost/phpmyadmin/
```

These are local development URLs only.

Production domains must be provided through environment configuration.

---

## 8. Local Database

Typical local development database configuration:

```text
Connection: mysql
Host:       127.0.0.1
Port:       3306
Database:   bstu_international
Username:   root
Password:   empty by default in the local development environment
```

These values are development examples only.

Production database credentials must:

* be environment-driven
* remain secret
* never be committed to Git
* never be printed in audit reports or application output

---

## 9. Backend Setup

Navigate to:

```bash
cd apps/api
```

---

## Install PHP Dependencies

```bash
composer install
```

Do not run `composer install` unnecessarily if dependencies are already present and valid.

---

## 10. Backend Environment

Create:

```text
apps/api/.env
```

from:

```text
apps/api/.env.example
```

Configure environment-specific values including:

* `APP_ENV`
* `APP_DEBUG`
* application URL
* frontend URL
* database connection
* Sanctum configuration
* CORS
* sessions
* cookies
* mail
* cache
* queue
* storage
* logging

Never commit `.env`.

---

## 11. Application Key

If the application key has not been generated:

```bash
php artisan key:generate
```

When the local Windows project requires its bundled PHP helper, use the established project helper instead.

Do not regenerate a valid application key unnecessarily on an existing environment.

---

## 12. Laravel Artisan on Windows

The repository may provide:

```text
apps/api/php-local.bat
```

for Laravel Artisan commands in the configured Windows development environment.

Examples:

```bash
.\php-local.bat artisan route:list
```

```bash
.\php-local.bat artisan migrate
```

```bash
.\php-local.bat artisan db:seed
```

Prefer the existing helper when it is required by the project environment.

---

## 13. Database Migration

For an existing development database:

```bash
.\php-local.bat artisan migrate
```

To inspect migration status where needed:

```bash
.\php-local.bat artisan migrate:status
```

Do not run migrations blindly against an unknown database.

---

## 14. Database Seeding

When required:

```bash
.\php-local.bat artisan db:seed
```

or:

```bash
.\php-local.bat artisan migrate --seed
```

Seeders should remain safe and idempotent where the architecture expects them to be rerunnable.

Stable identifiers may include:

* slug
* code
* locale
* email
* other stable natural keys

---

## 15. Critical Database Safety

Never run the following against important, shared, staging, production, or unknown data:

```bash
php artisan migrate:fresh
```

```bash
php artisan migrate:fresh --seed
```

```bash
php artisan db:wipe
```

These operations can destroy existing data.

`migrate:fresh --seed` is acceptable only for a database positively identified as disposable local/test data.

Production-compatible database changes should generally be:

* additive
* reversible
* backward-compatible where practical
* safe for existing records

---

## 16. Start Backend

Use the local PHP environment configured for the repository.

Typical backend address:

```text
http://127.0.0.1:8000
```

If using PHP's built-in development server directly, ensure required extensions are loaded.

The exact command may depend on the local PHP configuration.

---

## 17. Verify Backend Routes

Use:

```bash
cd apps/api
.\php-local.bat artisan route:list
```

API routes are primarily defined in:

```text
apps/api/routes/api.php
```

---

## 18. Frontend Setup

Navigate to:

```bash
cd apps/web
```

Install dependencies when needed:

```bash
npm install
```

Do not run dependency installation repeatedly without need.

---

## 19. Start Frontend

```bash
npm run dev
```

Typical Vite URL:

```text
http://localhost:5173
```

---

## 20. Frontend Static Verification

Lint:

```bash
npm.cmd run lint
```

Production build:

```bash
npm.cmd run build
```

Use the actual scripts defined in `package.json`.

A successful build proves that the production bundle was generated successfully.

It does **not** prove that every page works correctly at runtime.

---

## 21. Page-Level Verification

For production-readiness auditing, use:

```text
SKILL.md
```

Page verification should include where applicable:

* browser runtime
* console errors
* browser network requests
* API response contracts
* frontend/API compatibility
* responsive rendering
* localization
* RTL/LTR
* Admin/CMS management
* database relationships
* validation
* authentication
* authorization
* media/storage
* SEO
* accessibility
* performance
* production configuration
* build verification

The following are not sufficient by themselves:

```text
HTTP 200
```

or:

```text
npm run build
```

---

## 22. Admin Panel

The administrative interface is part of the React application.

Typical local login route:

```text
http://localhost:5173/apanel/login
```

Local seeded development environments may include an Admin/Apanel account.

Example identity:

```text
Email: apanel@bstu.uz
Role:  apanel
```

Any default seeded password is intended only for development/testing.

---

## 23. Admin Credential Security

Never deploy production with:

* a known default Admin password
* development test credentials
* seeded credentials that have not been changed
* credentials stored in source code

Production Admin credentials must be securely managed.

Do not publish real credentials in:

* README files
* code
* screenshots
* logs
* Git history
* audit reports

---

## 24. Dynamic CMS Content

Public CMS-managed content should normally use:

```text
Admin / CMS
      ↓
Laravel API
      ↓
MySQL
      ↓
Public API
      ↓
React
```

Important public dynamic content should have an intentional management path.

Examples may include:

* homepage content
* programs
* faculties
* departments
* services
* announcements
* news
* blog
* administration/leadership
* videos
* images
* documents
* SEO
* translations

Actual management architecture must be determined from the repository.

---

## 25. Content Source of Truth

The production runtime source of truth should normally be:

```text
MySQL
+
Laravel APIs
+
Admin/CMS
```

Historical/static frontend files are not automatically production sources.

Do not silently use old React/Dart data as fallback production content if the current architecture is database-backed.

---

## 26. Backend Seed/Data Sources

Reviewed durable seed content may exist under:

```text
apps/api/database/data/
```

and related Laravel seeders.

Historical import files should not become runtime dependencies after their data has been migrated into the supported backend data architecture.

---

## 27. Localization Architecture

Active locales are database-driven.

Locale configuration may include:

* locale code
* language name
* active/inactive state
* text direction

Locale management is available through the Admin/CMS system.

---

## 28. General UI Translations

General UI translation data may be retrieved through endpoints such as:

```text
GET /api/v1/translations?locale=...
```

Individual CMS resources may use their own translation tables and localized APIs.

Do not assume every localized field comes from the global translation endpoint.

Inspect each resource's actual architecture.

---

## 29. RTL / LTR

Arabic normally uses:

```text
RTL
```

English, Uzbek, and Russian normally use:

```text
LTR
```

Frontend locale handling should set appropriate document values such as:

```html
<html lang="..." dir="...">
```

RTL/LTR behavior should be verified in the real browser for production-readiness audits.

---

## 30. Media & Storage

Laravel-managed uploads use the established public storage architecture.

Public path:

```text
apps/api/public/storage
```

points to:

```text
storage/app/public
```

through Laravel's public storage link/junction.

It is not a duplicate independent upload directory.

Use existing storage/media URL helpers.

Do not hardcode Windows filesystem paths into frontend source.

---

## 31. Missing Media

Public UI should gracefully handle missing or optional media.

Avoid rendering invalid markup such as:

```html
<img src="">
```

Use:

* appropriate fallback UI
* placeholder
* intentionally omitted media

depending on the existing project design.

---

## 32. Authentication

The backend uses:

```text
Laravel Sanctum
```

Authentication-sensitive operations must preserve the existing security model.

Do not bypass login or token/session requirements simply to make an endpoint work.

---

## 33. Authorization

Authorization is separate from authentication.

Protected Admin operations should distinguish between:

```text
Unauthenticated
Authenticated but unauthorized
Authorized administrator
```

A `401` response does not prove role authorization is correct.

Role/policy/middleware behavior should be tested independently where relevant.

---

## 34. Public Caching

Public CMS content may use cache versioning through:

```text
public_content_cache_version
```

Private student or Admin data must not be treated as public cacheable content.

---

## 35. Cache Maintenance

Relevant development commands may include:

```bash
.\php-local.bat artisan cache:clear
```

and:

```bash
.\php-local.bat artisan optimize:clear
```

Do not clear caches unnecessarily.

Use cache clearing only when required for diagnosis, configuration changes, or content verification.

---

## 36. Production Environment

Production must use environment-specific configuration.

Typical Laravel requirements include:

```text
APP_ENV=production
APP_DEBUG=false
```

Production configuration should also define appropriate values for:

* application URL
* frontend URL
* backend/API URL
* database
* CORS
* Sanctum domains
* sessions
* token settings
* secure cookies
* HTTPS
* mail
* cache
* queue
* logging
* storage

---

## 37. Local vs Production Configuration

A local environment may legitimately use:

```text
APP_ENV=local
APP_DEBUG=true
```

This is not automatically a defect.

Production-readiness verification should confirm that the deployment environment can safely use production configuration.

Do not change the developer's local `.env` to production merely to obtain a positive audit result.

---

## 38. Production CORS

Production CORS configuration should be restricted intentionally.

Avoid broad wildcard origins unless explicitly required and reviewed.

Production frontend domains should be configured through environment/configuration mechanisms rather than source-code hardcoding.

---

## 39. Sanctum & Sessions

Production must configure appropriately:

* stateful domains
* session domains
* cookie security
* SameSite behavior
* Secure cookies
* token expiration where applicable
* HTTPS

Do not weaken these values to make local testing easier.

---

## 40. Production Cache / Queue

Production should use appropriate persistent infrastructure where required.

Avoid relying on local-development-only cache or queue behavior if the deployed application requires persistent stores.

---

## 41. Logging

Production logging should:

* avoid exposing secrets
* avoid debug stack traces to public users
* support rotation or appropriate log management
* preserve enough diagnostics for operational troubleshooting

---

## 42. Security Principles

Never expose:

* passwords
* API keys
* private tokens
* database credentials
* secret environment values
* internal stack traces
* filesystem paths

Never disable:

* authentication
* authorization
* CSRF
* validation
* output escaping

simply to make tests pass.

---

## 43. Package Management

Do not manually edit:

```text
package-lock.json
```

or:

```text
node_modules/.package-lock.json
```

npm lockfile version `3` is expected for the frontend environment.

Use npm itself when dependencies genuinely need to change.

---

## 44. Browser / E2E Dependencies

Do not permanently add large testing dependencies merely for one disposable audit if:

* an existing tool is available
* an installed browser can be used
* a temporary non-invasive approach is sufficient

For audit behavior, follow `SKILL.md`.

---

## 45. Git Safety

Before completing significant implementation work, inspect:

```bash
git status
```

and:

```bash
git diff
```

Distinguish:

* changes created during the current task
* pre-existing user/developer changes

Do not silently revert unrelated work.

---

## 46. Generated / Temporary Files

Disposable artifacts may include:

* browser screenshots
* E2E reports
* test results
* temporary scripts
* debug logs
* audit values
* diagnostic exports

These should not become production source unless intentionally adopted as permanent testing infrastructure.

The repository `.gitignore` excludes common temporary artifacts.

---

## 47. Temporary Agent Directories

Directories such as:

```text
.agents/
.kilo/
```

are temporary scratch spaces.

They should not become durable project source-of-truth.

Long-lived documentation belongs in:

```text
README.md
AGENTS.md
SKILL.md
```

or appropriate source/data files.

---

## 48. Development Principles

1. Preserve the monorepo architecture.
2. Keep React in `apps/web`.
3. Keep Laravel in `apps/api`.
4. Use the existing REST API.
5. Treat database-backed CMS data as runtime source-of-truth.
6. Protect existing database data.
7. Prefer additive/reversible migrations.
8. Preserve authentication.
9. Preserve authorization.
10. Preserve localization architecture.
11. Preserve media/storage architecture.
12. Fix root causes.
13. Avoid hardcoded production data.
14. Avoid hidden runtime fallbacks.
15. Test meaningful changes.
16. Verify browser behavior for UI changes.
17. Review regression impact for shared changes.
18. Keep production configuration environment-driven.
19. Never commit secrets.
20. Inspect Git changes before finishing.

---

## 49. Coding Agent Workflow

Coding agents should follow:

```text
Read AGENTS.md
      ↓
Understand the task
      ↓
Inspect the real implementation
      ↓
Trace dependencies
      ↓
Make the smallest safe change
      ↓
Run relevant verification
      ↓
Inspect regression impact
      ↓
Inspect git diff/status
      ↓
Report accurately
```

For a page/route audit:

```text
Read AGENTS.md
      ↓
Read SKILL.md
      ↓
Resolve target
      ↓
Perform end-to-end audit
      ↓
Repair safe confirmed issues
      ↓
Retest
      ↓
Production-readiness verdict
```

---

## 50. Page Audit Examples

## Exact Page

```text
Audit:
http://localhost:5173/about
```

## Route Family

```text
Audit:
http://localhost:5173/announcements/*
```

## List + Detail Family

```text
Audit these as one connected feature:

http://localhost:5173/announcements
http://localhost:5173/announcements/*
```

The coding agent should follow `SKILL.md` automatically when these requests are recognized.

---

## 51. Documentation Responsibilities

## README.md

Maintain:

* project overview
* setup instructions
* developer workflow
* architecture overview
* environment/deployment basics

---

## AGENTS.md

Maintain:

* coding-agent behavior
* architecture constraints
* implementation conventions
* repository safety
* database/security/Git rules

---

## SKILL.md

Maintain:

* page-audit protocol
* end-to-end verification
* browser testing
* CMS/database verification
* repair workflow
* production-readiness criteria

Keep these responsibilities separate as the repository evolves.

---

## 52. Documentation Source of Truth

Avoid maintaining duplicate copies of the same durable instructions.

In particular:

```text
SKILL.md
```

should remain the single authoritative page-audit skill.

Do not maintain a second outdated copy such as:

```text
page-audit-skill.md
```

unless there is an explicit reason to keep it synchronized.

---

## 53. Final Developer Checklist

Before committing meaningful implementation changes:

* application architecture preserved
* database safety considered
* relevant lint/tests executed
* relevant build executed
* browser behavior verified where applicable
* shared dependencies checked
* no secrets exposed
* no temporary audit data remains
* `git diff` reviewed
* `git status` reviewed
* documentation updated if architecture changed

---

## 54. Repository Goal

The repository should remain:

* maintainable
* data-safe
* secure
* multilingual
* administratively manageable
* production-oriented
* auditable
* deployment-ready

Changes should improve the existing architecture rather than bypass it.

# BSTU International Website — Coding Agent Guide

This repository is an existing monorepo for the **BSTU International system**.

Do not restart the project, recreate Laravel, move `apps/web`, create a new React application, or replace the established architecture unless explicitly requested.

---

## Project Root

The repository root is:

```text
C:\Users\KAPAKA\Desktop\international.bstu.uz
```

Important repository files:

```text
AGENTS.md
SKILL.md
README.md
international.bstu.uz.code-workspace
```

Applications:

```text
apps/web
apps/api
```

When working from this repository, treat the repository root as the working project context unless the task explicitly requires a narrower directory.

---

## Project Layout

## Frontend

```text
apps/web
```

React 19 + Vite + Tailwind CSS 4 application containing:

* Public international website
* Student portal
* `apanel`
* Shared frontend components
* Frontend routing
* Public/API client services
* Localization/UI infrastructure

## Backend

```text
apps/api
```

Laravel 13 REST API using:

* MySQL
* Laravel Sanctum
* Public APIs
* Student APIs
* Apanel/Admin APIs
* CMS/database-backed content
* Media/storage handling

---

## Page Audit Skill

The repository contains the official reusable page-audit skill:

```text
SKILL.md
```

Absolute local path:

```text
C:\Users\KAPAKA\Desktop\international.bstu.uz\SKILL.md
```

This skill defines the required procedure for:

* Page audits
* Route audits
* Route-family audits
* Frontend verification
* Backend/API verification
* Database verification
* Admin/CMS verification
* CRUD/synchronization verification
* Browser runtime testing
* Responsive testing
* i18n/RTL testing
* Authentication/authorization testing
* Security review
* Media/storage verification
* SEO review
* Performance review
* Production-readiness verification
* Safe repair and retesting

## Mandatory Skill Activation

When the user asks to:

* audit a page
* review a page
* verify a page
* test a page
* repair a page
* prepare a page for production
* inspect whether a page is ready for deployment
* review a route or route family

and provides a target such as:

```text
http://localhost:5173/about
```

or:

```text
http://localhost:5173/announcements/*
```

or equivalent local/application routes,

you MUST read and follow:

```text
SKILL.md
```

before performing the page audit.

Do not require the user to paste the contents of `SKILL.md`.

Do not ask the user to repeat the instructions already defined there.

Begin the audit immediately after resolving the target.

---

## Page Audit Invocation Behavior

A user may invoke an audit simply by saying, for example:

```text
Audit:
http://localhost:5173/about
```

or:

```text
Audit:
http://localhost:5173/announcements/*
```

or:

```text
Review according to SKILL.md:
http://localhost:5173/about
```

Treat these as instructions to use the repository's `SKILL.md`.

The user does not need to provide the full absolute skill path every time.

---

## Route Family Semantics

When an audit target contains:

```text
/*
```

for example:

```text
http://localhost:5173/announcements/*
```

do not interpret it as one literal browser URL.

Treat it as a request to audit the actual route family under that path.

Discover real child/dynamic routes from the project's router.

Examples may include routes such as:

```text
/announcements/:id
/announcements/:slug
```

but never invent routes that are not actually implemented.

If the user provides both:

```text
http://localhost:5173/announcements
http://localhost:5173/announcements/*
```

audit them as one connected feature family:

```text
Index/List
→ Detail/Dynamic Routes
→ APIs
→ Database
→ Admin/CMS
→ Browser Runtime
```

Avoid duplicating work unnecessarily.

---

## Skill Precedence

For a page/route audit:

1. Follow `AGENTS.md` for repository-wide architecture and safety rules.
2. Follow `SKILL.md` for the audit execution protocol.
3. Follow the user's current task-specific instructions.
4. Preserve existing project architecture and user work.

If instructions conflict, do not silently choose the riskier behavior.

Repository safety and non-destructive database rules always remain in force.

---

## Audit Continuation

If the same target has already been audited during the active task and the user asks to continue verification:

* Continue from the existing verified state.
* Do not restart the entire audit unnecessarily.
* Re-run checks affected by new code changes.
* Use the final verification requirements defined in `SKILL.md`.
* Do not repeat already verified work unless regression testing requires it.

---

## Common Commands

## Web

```bash
cd apps/web

npm install
npm.cmd run lint
npm.cmd run build
npm.cmd run dev
```

Use the project's actual available scripts before inventing new commands.

Do not run `npm install` unnecessarily if dependencies are already correctly installed.

---

## API

```bash
cd apps/api

composer install
.\php-local.bat artisan route:list
.\php-local.bat artisan migrate
.\php-local.bat artisan db:seed
```

Do not run migrations or seeders blindly.

Before database-changing commands, determine whether the connected database is safe for the operation.

---

## Architecture Rules

* Keep the React frontend in `apps/web`.
* Keep Laravel API code in `apps/api`.
* Use the existing REST API and MySQL-backed content architecture whenever applicable.
* Preserve the public website.
* Preserve `apanel`.
* Preserve the student system.
* Preserve existing Laravel API routes unless a confirmed issue requires a compatible change.
* Do not create parallel APIs or duplicate CMS systems without a confirmed architectural requirement.
* Do not add absolute Windows paths to frontend source or production bundles.
* Do not render source-code paths, audit markers, test labels, or debug labels publicly.
* Do not replace database-managed content with hardcoded frontend content.
* Fix root causes rather than masking broken connections.

---

## Frontend Conventions

* Pages are lazy-loaded in `apps/web/src/App.jsx`.
* Shared public layout uses `Header`, `Footer`, and `ScrollToTop`.
* Use `useLanguage()` / `useLocale()` for visible labels and locale switching.
* Arabic uses RTL through `LocaleContext`, which sets document `lang` and `dir`.
* Preserve existing Tailwind CSS 4 conventions.
* Use existing responsive patterns before introducing new ones.
* Use Lucide React icons when icons are needed.
* Add meaningful `alt` text to images.
* Provide safe fallback behavior when CMS/media assets may be missing.
* Do not render `<img src="">`.
* External links opened in a new tab must use:

```jsx
target="_blank"
rel="noopener noreferrer"
```

* Do not suppress React/runtime errors simply to make builds pass.
* Preserve accessibility and keyboard usability.
* Verify responsive changes at runtime when browser tooling is available.

---

## Backend Conventions

* API routes live in:

```text
apps/api/routes/api.php
```

* Public APIs are handled primarily by:

```text
Api\PublicApiController
```

* Student workflows are handled by:

```text
Api\StudentApiController
```

* Apanel CRUD is handled primarily by:

```text
Api\AdminCrudController
```

Some specialized CMS resources may use dedicated controllers. Always inspect actual routes before assuming controller ownership.

* Database content is seeded from:

```text
apps/api/database/data/
```

and relevant seeder classes.

* Public database-backed content is the runtime source of truth.

Static React/Dart data files are migration/reference sources only unless current project code explicitly establishes otherwise.

* Legacy React import scripts were removed after reviewed content was merged into:

```text
apps/api/database/data/
```

Do not restore `apps/web/src/data` as a runtime production-content or translation fallback system.

---

## Database Rules

Database changes must prioritize existing data safety.

Use additive/backward-compatible migrations whenever practical.

Never automatically run against shared or real data:

```text
migrate:fresh
migrate:fresh --seed
db:wipe
```

Do not:

* truncate important tables
* delete real records for convenience
* remove important columns blindly
* rewrite migration history recklessly
* run destructive seeders against non-disposable databases

`migrate:fresh --seed` is acceptable only for a database positively identified as disposable.

Keep seeders idempotent using stable identifiers such as:

* slugs
* codes
* locales
* emails
* other stable natural keys

Before reversible CMS mutation testing, follow the database-environment safety rules defined in `SKILL.md`.

---

## Media / Storage Rules

Keep uploads on Laravel's public disk and use existing shared media/storage helpers.

Preserve:

```text
apps/api/public/storage
```

It is a Laravel public link/junction to:

```text
storage/app/public
```

It is not a duplicate upload directory.

Do not replace the storage architecture with frontend-local files.

Avoid hardcoded:

```text
localhost
127.0.0.1
C:\...
```

inside production media URLs or public source code.

---

## Authentication / Authorization

Use the established authentication and authorization architecture.

Do not bypass:

* Laravel Sanctum
* roles
* policies
* middleware
* CSRF protections
* admin permission checks

Authentication and authorization are separate concerns.

For relevant protected routes, distinguish between:

```text
Unauthenticated
Authenticated but unauthorized
Authorized admin
```

Do not treat a `401` unauthenticated response as proof that role authorization also works.

---

## Production Rules

Keep production configuration environment-driven.

Production should use appropriate values including:

```text
APP_DEBUG=false
```

and production-specific:

* CORS origins
* Sanctum domains
* session configuration
* secure cookies
* HTTPS behavior
* cache store
* queue store
* logging configuration

Do not treat:

```text
APP_ENV=local
APP_DEBUG=true
```

in a local development environment as a defect by themselves.

Instead verify that production values can be configured correctly.

Do not modify the developer's local environment to production mode merely to obtain a successful audit verdict.

---

## Caching

Do not cache private:

* student responses
* apanel responses
* authentication-sensitive data

Public CMS caching is versioned through:

```text
public_content_cache_version
```

Relevant public cache can be reset using appropriate existing mechanisms such as:

```bash
cache:clear
optimize:clear
```

Do not clear caches unnecessarily.

---

## Package Management

Do not manually edit:

```text
package-lock.json
node_modules/.package-lock.json
```

npm lockfile version `3` is expected for this project.

Use npm itself when dependency changes are genuinely required.

Do not add permanent dependencies solely for a one-time verification when a safer temporary/non-invasive method exists.

---

## Git / Change Safety

Before finishing meaningful code work, inspect:

```bash
git diff
git status
```

Distinguish:

* changes made during the current task
* pre-existing user changes

Do not silently revert unrelated user work.

Do not overwrite unrelated untracked files.

If modifying shared code, inspect likely consumers and regression impact.

---

## Documentation

Keep durable project guidance in:

```text
README.md
AGENTS.md
SKILL.md
```

or appropriate active source comments/data files.

Use:

* `AGENTS.md` for general repository-wide agent guidance.
* `SKILL.md` for the reusable end-to-end page audit protocol.
* `README.md` for durable project/developer documentation.

Temporary agent/task folders such as:

```text
.agents/
.kilo/
```

are ignored and should not become project source-of-truth.

Historical source captures should be merged into durable seed/data files rather than kept as runtime dependencies.

---

## Agent Operating Principle

When working in this repository:

```text
Understand existing architecture
→ trace real implementation
→ make the smallest safe change
→ test it
→ verify runtime behavior where applicable
→ inspect regression impact
→ inspect final diff
→ report evidence accurately
```

Never prioritize producing a positive result over producing a correct result.

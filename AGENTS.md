# BSTU International Website — Coding Agent Guide

This repository is an existing monorepo for the **BSTU International system**.

Do not restart the project, recreate Laravel, move `apps/web`, create a new React application, create a parallel backend, or replace the established architecture unless the user explicitly requests an architectural change.

The default objective is to preserve and improve the existing system safely.

---

## 1. REPOSITORY ROOT

Repository root:

```text
C:\Users\KAPAKA\Desktop\international.bstu.uz
```

Primary repository documentation:

```text
README.md
AGENTS.md
SKILL.md
international.bstu.uz.code-workspace
```

Applications:

```text
apps/web
apps/api
```

When working in this repository, treat:

```text
C:\Users\KAPAKA\Desktop\international.bstu.uz
```

as the repository/project root unless the task explicitly requires a narrower scope.

Do not treat `apps/web` or `apps/api` as separate independent projects when repository-level context matters.

---

## 2. DOCUMENT RESPONSIBILITIES

The repository uses three durable documentation layers.

## README.md

Use for:

* project overview
* developer setup
* architecture summary
* local development
* deployment basics
* general developer documentation

## AGENTS.md

Use for:

* repository-wide coding-agent instructions
* architecture constraints
* implementation conventions
* database safety
* security rules
* Git/change safety
* agent operating behavior

## SKILL.md

Use for:

* page audits
* route audits
* route-family audits
* end-to-end verification
* page repair
* browser/runtime verification
* production-readiness decisions

Do not duplicate the complete contents of one file into another.

Use each document for its intended scope.

---

## 3. INSTRUCTION PRECEDENCE

When operating inside this repository, follow instructions in this order:

1. System/tool/runtime safety requirements.
2. The user's current explicit task.
3. Repository-wide rules in `AGENTS.md`.
4. Specialized procedure in `SKILL.md` when the task is a page/route audit.
5. Durable project context from `README.md`.
6. Existing codebase conventions discovered from the repository.

For page/route audits:

```text
AGENTS.md
    ↓
SKILL.md
    ↓
Target-specific user request
```

Repository safety and non-destructive database rules always remain in force.

If instructions conflict, do not silently choose a more destructive or risky interpretation.

---

## 4. PROJECT LAYOUT

## Frontend

```text
apps/web
```

React 19 + Vite + Tailwind CSS 4 application containing:

* public international website
* student portal
* `apanel`
* frontend routing
* shared layouts
* shared components
* API clients
* localization infrastructure
* browser UI

## Backend

```text
apps/api
```

Laravel 13 REST API containing:

* public APIs
* student APIs
* Admin/Apanel APIs
* authentication
* authorization
* CMS
* database models
* migrations
* seeders
* validation
* media/storage handling
* localization/data APIs

Backend stack includes:

* PHP 8.3+
* Laravel 13
* Laravel Sanctum
* MySQL / InnoDB

---

## 5. PAGE AUDIT SKILL

The official reusable page-audit protocol is:

```text
SKILL.md
```

Absolute local path:

```text
C:\Users\KAPAKA\Desktop\international.bstu.uz\SKILL.md
```

`SKILL.md` is the authoritative procedure for:

* page audits
* route audits
* route-family audits
* frontend verification
* backend/API verification
* database verification
* Admin/CMS verification
* CRUD/synchronization verification
* browser runtime verification
* responsive verification
* i18n/RTL verification
* authentication/authorization verification
* security review
* accessibility review
* media/storage verification
* SEO review
* performance review
* production configuration review
* production-readiness decisions
* safe repairs
* retesting
* regression verification

---

## 6. MANDATORY SKILL ACTIVATION

Read and follow `SKILL.md` whenever the user asks to:

* audit a page
* audit a route
* audit a route family
* review a page end-to-end
* verify a page
* test whether a page is production-ready
* repair a page and verify it
* inspect a route for production readiness
* perform an end-to-end page review
* review list/detail route families

Examples:

```text
Audit:
http://localhost:5173/about
```

```text
Audit:
http://localhost:5173/announcements/*
```

```text
Audit these as one connected feature:

http://localhost:5173/announcements
http://localhost:5173/announcements/*
```

When such a task is detected:

1. Read `SKILL.md`.
2. Resolve the supplied target.
3. Begin immediately.
4. Do not ask the user to paste `SKILL.md`.
5. Do not ask the user to repeat instructions already defined there.

---

## 7. WHEN NOT TO ACTIVATE SKILL.md

Do not automatically run the full page-audit protocol for unrelated tasks such as:

* a small text change
* one isolated bug fix
* a Git commit message
* translating content
* changing a configuration value
* adding one specific component
* investigating a backend-only issue
* answering a repository question

Use `SKILL.md` only when the task actually involves page/route auditing, verification, production readiness, or when the user explicitly asks to use it.

---

## 8. ROUTE FAMILY SEMANTICS

When the audit target contains:

```text
/*
```

for example:

```text
http://localhost:5173/announcements/*
```

do not interpret the wildcard as a literal browser URL.

Treat it as a request to audit the actual route family below that path.

Inspect the project's router and discover real child/dynamic routes.

Possible examples may include:

```text
/announcements/:id
/announcements/:slug
```

These are examples only.

Never invent routes that do not exist.

If the user provides both:

```text
http://localhost:5173/announcements
http://localhost:5173/announcements/*
```

treat them as one connected feature:

```text
Index/List
    ↓
Detail/Dynamic Route
    ↓
API
    ↓
Backend
    ↓
Database
    ↑
Admin/CMS
    ↓
Browser Verification
```

Avoid unnecessary duplicate work.

---

## 9. AUDIT CONTINUATION

If the same target is already being audited in the active task:

* continue from the existing verified state
* do not restart the entire audit unnecessarily
* reuse valid evidence
* invalidate only checks affected by subsequent code changes
* rerun downstream regression checks when needed
* follow the verification-ledger rules defined in `SKILL.md`

For example, if only responsive frontend code changed, do not unnecessarily rediscover the entire database schema unless that dependency was affected.

---

## 10. COMMON WEB COMMANDS

Frontend directory:

```bash
cd apps/web
```

Typical commands:

```bash
npm install
npm.cmd run lint
npm.cmd run build
npm.cmd run dev
```

Use the scripts actually defined in the repository.

Do not run:

```bash
npm install
```

unnecessarily when dependencies are already correctly installed.

Do not manually edit generated npm lockfiles.

---

## 11. COMMON API COMMANDS

Backend directory:

```bash
cd apps/api
```

Typical commands:

```bash
composer install
.\php-local.bat artisan route:list
.\php-local.bat artisan migrate
.\php-local.bat artisan db:seed
```

Do not run migrations or seeders blindly.

Determine database safety before executing database-changing commands.

Prefer repository-provided PHP helpers where they are required by the local Windows environment.

---

## 12. ARCHITECTURE RULES

* Keep React frontend code inside `apps/web`.
* Keep Laravel backend/API code inside `apps/api`.
* Preserve the monorepo architecture.
* Preserve the public website.
* Preserve the student portal.
* Preserve `apanel`.
* Preserve Laravel REST API architecture.
* Use the existing MySQL-backed CMS/content architecture.
* Do not create duplicate APIs.
* Do not create parallel CMS implementations.
* Do not create duplicate localization systems.
* Do not replace database-backed content with frontend hardcoded content.
* Do not add absolute local Windows paths to production source or bundles.
* Do not render source paths, debug labels, audit values, or internal implementation details publicly.
* Preserve backward compatibility whenever practical.
* Prefer root-cause fixes.

---

## 13. FRONTEND CONVENTIONS

Pages are primarily routed from:

```text
apps/web/src/App.jsx
```

Pages may be lazy-loaded.

Shared public layout uses established components such as:

```text
Header
Footer
ScrollToTop
```

Use existing locale hooks such as:

```text
useLanguage()
useLocale()
```

for visible localized content and language switching where applicable.

Arabic uses RTL through the established locale/context architecture.

The active locale system controls document:

```text
lang
dir
```

Preserve existing Tailwind CSS 4 conventions.

Use existing visual patterns before introducing new design systems.

Use Lucide React icons when icons are needed unless the existing component already uses another established project pattern.

---

## 14. FRONTEND QUALITY RULES

When modifying frontend code:

* preserve responsive behavior
* preserve RTL/LTR behavior
* preserve accessibility
* preserve keyboard usability
* use semantic HTML
* provide meaningful image alt text
* handle nullable API data safely
* provide image/media fallback where appropriate
* avoid layout overflow
* avoid hardcoded dynamic content
* avoid dead controls
* avoid unnecessary duplicate API requests
* avoid debug output in production UI

Do not render:

```jsx
<img src="">
```

When the image URL may be missing, render a safe fallback or omit the image intentionally.

External links opened in a new tab must use:

```jsx
target="_blank"
rel="noopener noreferrer"
```

Do not suppress React/runtime errors merely to make lint/build pass.

---

## 15. BACKEND CONVENTIONS

Primary Laravel API route file:

```text
apps/api/routes/api.php
```

Public APIs are handled primarily by:

```text
Api\PublicApiController
```

Student workflows are handled primarily by:

```text
Api\StudentApiController
```

Apanel/Admin CRUD is handled primarily by:

```text
Api\AdminCrudController
```

Specialized CMS features may use dedicated controllers.

Always inspect the real route/controller implementation before assuming ownership.

Do not force every Admin resource through `AdminCrudController` if the project already has a specialized implementation.

---

## 16. DATABASE CONTENT SOURCE OF TRUTH

Database-backed public content should normally be treated as runtime source-of-truth.

Runtime content architecture should generally remain:

```text
Admin/CMS
    ↓
Laravel
    ↓
MySQL
    ↓
Public API
    ↓
React
```

Database content may be seeded from:

```text
apps/api/database/data/
```

and associated seeder classes.

Historical/static React or Dart data files are reference/migration sources only unless current project code explicitly establishes them as runtime dependencies.

Legacy React import scripts were removed after their reviewed content was migrated into durable backend data.

Do not reintroduce:

```text
apps/web/src/data
```

as a production runtime fallback for database-managed content or translations.

---

## 17. DATABASE RULES

Database changes must prioritize preserving existing data.

Prefer:

* additive migrations
* backward-compatible migrations
* reversible migrations
* safe indexes
* safe constraints

Do not automatically run against shared, important, or unknown data:

```text
migrate:fresh
migrate:fresh --seed
db:wipe
```

Do not:

* truncate important tables
* delete real records for convenience
* blindly remove columns
* rewrite migration history unnecessarily
* run destructive seeders on non-disposable databases

`migrate:fresh --seed` is acceptable only when the database has been positively identified as disposable local/test data.

---

## 18. SEEDER RULES

Keep seeders idempotent where practical.

Use stable identifiers such as:

* slug
* code
* locale
* email
* other stable natural identifiers

Do not generate uncontrolled duplicate records every time a seeder runs.

Seed data should be safe to rerun where the current project architecture expects that behavior.

---

## 19. DATABASE MUTATION SAFETY

Before page-audit CMS mutation tests, follow the database classification and rollback rules defined in `SKILL.md`.

Never assume:

```text
localhost
```

means:

```text
safe disposable database
```

A local database may contain important user/project data.

If environment safety is unclear, treat the database as important.

---

## 20. MEDIA / STORAGE RULES

Use Laravel's established storage architecture.

Preserve:

```text
apps/api/public/storage
```

as the Laravel public link/junction to:

```text
storage/app/public
```

It is not a second upload directory.

Use existing media/storage URL helpers.

Do not move server-managed uploaded files into frontend source directories.

Do not hardcode paths such as:

```text
C:\...
```

into public frontend code.

Avoid production dependencies on:

```text
localhost
127.0.0.1
```

unless intentionally provided through local environment configuration.

---

## 21. AUTHENTICATION

Use the existing Laravel Sanctum architecture.

Do not bypass:

* authentication middleware
* session security
* token validation
* CSRF protections
* login protections

Do not remove authentication requirements merely to make a feature work.

---

## 22. AUTHORIZATION

Authentication and authorization are separate.

For protected operations distinguish:

```text
Unauthenticated
Authenticated but unauthorized
Authorized admin
```

Do not treat:

```text
401
```

as proof that role/permission authorization works.

Preserve:

* roles
* policies
* permission middleware
* ownership checks
* Admin access boundaries

---

## 23. SECURITY RULES

Never weaken:

* input validation
* authorization
* authentication
* CSRF protection
* file upload validation
* database safety
* output escaping

Do not expose:

* credentials
* API keys
* tokens
* private environment values
* database passwords
* internal filesystem paths
* stack traces

Do not print secret values in audit reports.

Security testing must remain safe and non-destructive.

Do not perform uncontrolled:

* brute-force attempts
* denial-of-service testing
* destructive fuzzing
* destructive penetration testing

unless explicitly authorized in an appropriate isolated environment.

---

## 24. PRODUCTION CONFIGURATION

Production configuration must remain environment-driven.

Production should support appropriate values including:

```text
APP_ENV=production
APP_DEBUG=false
```

and production-specific configuration for:

* application URL
* frontend URL
* backend URL
* database
* CORS
* Sanctum
* sessions
* secure cookies
* HTTPS
* mail
* storage
* cache
* queue
* logging

Do not treat:

```text
APP_ENV=local
APP_DEBUG=true
```

inside a developer's local environment as defects by themselves.

Instead verify that production configuration can use safe production values.

Do not change a developer's local environment into production mode just to make an audit report look better.

---

## 25. CORS / SANCTUM / SESSION RULES

Production environments should use intentionally restricted configuration.

Do not broaden:

* CORS origins
* Sanctum stateful domains
* cookie domains
* session scope

without understanding production implications.

Avoid wildcard production configuration unless it is explicitly required and secure.

---

## 26. CACHING

Do not cache private:

* student responses
* apanel responses
* authorization-sensitive responses
* authentication-sensitive data

Public CMS caching uses the existing cache-versioning architecture, including:

```text
public_content_cache_version
```

Where necessary, existing cache mechanisms may include:

```bash
.\php-local.bat artisan cache:clear
.\php-local.bat artisan optimize:clear
```

Do not clear caches unnecessarily.

---

## 27. PACKAGE MANAGEMENT

Do not manually edit:

```text
package-lock.json
node_modules/.package-lock.json
```

npm lockfile version `3` is expected.

Use npm for actual dependency modifications.

Do not add permanent dependencies solely for a disposable audit when an existing or temporary non-invasive tool is sufficient.

When a permanent dependency is genuinely needed, use the package manager and verify the resulting lockfile.

---

## 28. BROWSER / E2E TOOLING

For browser verification, prefer existing project tooling.

Possible options may include:

* Playwright
* Cypress
* Puppeteer
* Selenium
* Chrome
* Chromium
* Edge
* Firefox

Do not assume Playwright is mandatory.

Do not stop browser verification merely because Playwright is absent.

Follow the browser-tooling discovery rules in `SKILL.md`.

---

## 29. BUILD / STATIC CHECKS

Use actual repository commands.

Applicable checks may include:

```text
lint
typecheck
production build
backend tests
frontend tests
feature tests
integration tests
E2E tests
route:list
migration status
schema verification
```

Do not claim a command passed unless it was actually executed.

Do not blindly repair unrelated project-wide failures.

Classify failures as:

```text
TARGET-RELATED
```

or:

```text
PRE-EXISTING / UNRELATED
```

where appropriate.

---

## 30. GIT / CHANGE SAFETY

Before finishing meaningful source changes, inspect:

```bash
git diff
git status
```

Distinguish:

* changes created during the current task
* pre-existing user/developer changes

Do not claim ownership of pre-existing work.

Do not silently revert unrelated changes.

Do not overwrite unrelated untracked files.

Do not use broad cleanup commands that could remove user work without explicit need.

---

## 31. SHARED DEPENDENCY SAFETY

Before changing shared:

* components
* hooks
* contexts
* layouts
* services
* API clients
* controllers
* models
* middleware
* database tables
* translation resources
* utilities

search for other consumers.

Do not fix one route by knowingly breaking another.

Run reasonable regression checks after shared changes.

---

## 32. ROOT-CAUSE REPAIR PRINCIPLE

Prefer:

```text
Verify issue
→ identify root cause
→ smallest safe fix
→ retest
→ regression check
```

Avoid:

* superficial suppression
* arbitrary fallback values
* hidden errors
* disabled validation
* disabled lint rules
* unnecessary `any`
* `@ts-ignore`
* `@ts-nocheck`
* removed authorization
* hardcoded replacements for dynamic content

unless there is a strong established architectural reason.

---

## 33. TEMPORARY AUDIT DATA

Temporary audit values, browser artifacts, screenshots, scripts, and helper files must not accidentally become production source.

After an audit:

* restore temporary CMS values
* remove temporary test data
* remove temporary helper scripts if not intentionally retained
* remove debug logs
* inspect `git status`
* inspect `git diff`

Use `.gitignore` for disposable generated artifacts where appropriate.

---

## 34. DOCUMENTATION

Durable project guidance belongs in:

```text
README.md
AGENTS.md
SKILL.md
```

or appropriate active source comments/data files.

Use:

```text
README.md
```

for developer documentation.

Use:

```text
AGENTS.md
```

for repository-wide agent rules.

Use:

```text
SKILL.md
```

for page-audit execution.

Do not use temporary agent scratch folders as permanent project source-of-truth.

Folders such as:

```text
.agents/
.kilo/
```

are temporary/ignored.

---

## 35. HISTORICAL / MIGRATION DATA

Historical source captures should not become permanent runtime dependencies unless explicitly required.

When historical content has been reviewed and migrated, preserve the durable version in:

* database
* seed data
* migrations
* active project data

Do not reintroduce obsolete source captures simply because they still exist somewhere locally.

---

## 36. AGENT AUTONOMY

For ordinary repository tasks, you may:

* inspect code
* search repository files
* inspect routes
* inspect models
* inspect configuration
* run safe diagnostics
* run relevant lint/tests/builds
* make target-related code changes
* add relevant tests
* inspect Git state

Do not automatically:

* delete real data
* reset important databases
* drop important tables
* truncate important data
* run destructive migrations
* expose secrets
* remove security controls
* perform unrelated large-scale refactors

If a destructive action appears necessary, report it instead of silently executing it.

---

## 37. AGENT OPERATING PRINCIPLE

When working in this repository, follow:

```text
Understand
    ↓
Trace
    ↓
Verify
    ↓
Diagnose
    ↓
Make smallest safe change
    ↓
Test
    ↓
Retest
    ↓
Regression check
    ↓
Inspect final diff
    ↓
Report accurately
```

Do not prioritize producing a positive result over producing a correct result.

---

## 38. FINAL WORK QUALITY

Before declaring a task complete:

* inspect modified files
* ensure syntax is correct
* ensure imports are correct
* ensure no duplicate code was introduced
* run relevant checks
* check shared impact where applicable
* inspect Git diff
* inspect Git status
* confirm no temporary debug/audit data remains
* confirm no secrets were exposed
* report limitations accurately

---

## 39. CORE NON-NEGOTIABLE RULES

1. Preserve the existing monorepo.
2. Do not recreate established applications.
3. Do not duplicate existing systems.
4. Do not replace database-managed content with hardcoded frontend content.
5. Do not weaken security.
6. Do not weaken validation.
7. Protect existing database data.
8. Prefer backward-compatible changes.
9. Use existing project conventions.
10. Fix root causes.
11. Test meaningful changes.
12. Verify browser changes at runtime when appropriate.
13. Check regression impact for shared changes.
14. Do not expose secrets.
15. Do not claim verification without evidence.
16. Keep production configuration environment-driven.
17. Do not confuse local development config with production readiness.
18. Use `SKILL.md` for page/route audits.
19. Continue active audits instead of unnecessarily restarting them.
20. Leave the repository safer and more reliable than you found it.

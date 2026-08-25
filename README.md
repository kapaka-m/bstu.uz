# Bukhara State Technical University (BSTU) International Website

Official monorepo for the **BSTU International Website**, including the public website, student portal, administrative panel (`apanel`), REST API, CMS, localization system, and database-backed content management.

---

## Repository Structure

```text
international.bstu.uz/
│
├── apps/
│   ├── web/                 # React frontend
│   └── api/                 # Laravel REST API
│
├── AGENTS.md                # Repository-wide coding agent instructions
├── SKILL.md                 # End-to-end page audit & production-readiness skill
├── README.md                # Developer/project documentation
├── international.bstu.uz.code-workspace
└── .gitignore
```

### Frontend Stack

```text
apps/web
```

Contains:

* Public BSTU International website
* Student portal
* `apanel` administrative interface
* Routing
* Localization UI
* API clients
* Shared components
* Public pages
* CMS management interfaces

### Backend

```text
apps/api
```

Contains:

* Laravel REST API
* Public APIs
* Student APIs
* Admin/CMS APIs
* Authentication and authorization
* MySQL models
* Migrations
* Seeders
* Media/storage handling
* Translation infrastructure

---

## Technology Stack

### Frontend

* React 19
* React Router 7
* Vite
* Tailwind CSS 4
* Framer Motion
* Swiper
* Lucide React
* React Context

### Backend Stack

* Laravel 13
* PHP 8.3+
* Laravel Sanctum
* MySQL / InnoDB
* REST API

### Localization Stack

Active languages are database-driven.

Default seeded locales:

* English — `en`
* Uzbek — `uz`
* Russian — `ru`
* Arabic — `ar`

Arabic uses RTL.

Other languages use their configured direction from the database.

---

## Architecture Overview

The application follows this general flow:

```text
React Frontend
      ↓
REST API
      ↓
Laravel Controllers / Services
      ↓
Eloquent Models
      ↓
MySQL
      ↑
Admin / CMS
```

Dynamic public content should normally follow:

```text
Admin / CMS
    ↓
Laravel API
    ↓
MySQL
    ↓
Public API
    ↓
React Website
```

The database/API layer is the runtime source of truth for managed content.

Do not move database-managed production content back into static frontend files.

---

## Important Agent Documentation

This repository contains two agent-related instruction files.

## `AGENTS.md`

```text
AGENTS.md
```

Contains repository-wide instructions covering:

* architecture
* frontend conventions
* backend conventions
* database safety
* authentication
* storage
* production configuration
* Git/change safety
* coding-agent behavior

Coding agents working inside this repository should read and follow `AGENTS.md`.

---

## `SKILL.md`

```text
SKILL.md
```

Contains the reusable **End-to-End Page Audit, Repair & Production Readiness Protocol**.

Use it when auditing pages or route families such as:

```text
http://localhost:5173/about
```

or:

```text
http://localhost:5173/announcements/*
```

The audit skill covers:

* Frontend
* Backend
* APIs
* Database
* Admin/CMS
* CRUD/synchronization
* Authentication/authorization
* Browser runtime
* Console/network
* Responsive design
* i18n / RTL
* Media/storage
* Security
* SEO
* Performance
* Production configuration
* Build verification
* End-to-end CMS data flow

Example Agent request:

```text
Audit according to the repository page-audit skill:

http://localhost:5173/about

Begin immediately.
```

For a route family:

```text
Audit according to the repository page-audit skill:

http://localhost:5173/announcements
http://localhost:5173/announcements/*

Treat them as one connected feature family.
Begin immediately.
```

---

## Local Development

## Requirements

Recommended local environment:

* Windows
* PHP 8.3+
* Composer
* Node.js / npm
* MySQL
* Git

The Laravel backend requires extensions including:

* `pdo_mysql`
* `fileinfo`
* `zip`

---

## Local Database

Default local development configuration:

```text
Connection: mysql
Host:       127.0.0.1
Port:       3306
Database:   bstu_international
Username:   root
Password:   empty by default in local development
```

phpMyAdmin, when installed locally:

```text
http://localhost/phpmyadmin/
```

These values are intended for local development only.

Production credentials must be configured through environment variables and must never be committed to the repository.

---

## Backend Setup

Navigate to:

```bash
cd apps/api
```

## Install Dependencies

```bash
composer install
```

## Configure Environment

Create:

```text
.env
```

from:

```text
.env.example
```

Configure:

* database
* application URL
* frontend URL
* Sanctum
* CORS
* mail
* cache
* queue
* storage
* other environment-specific settings

---

## Generate Application Key

Where standard PHP is correctly configured:

```bash
php artisan key:generate
```

If using the repository's local PHP helper, use the appropriate existing project command.

---

## Database Migration

For an existing development database:

```bash
.\php-local.bat artisan migrate
```

To seed required project data:

```bash
.\php-local.bat artisan db:seed
```

or, when appropriate:

```bash
.\php-local.bat artisan migrate --seed
```

## Important Database Safety Warning

Do **not** run:

```bash
php artisan migrate:fresh
```

or:

```bash
php artisan migrate:fresh --seed
```

against:

* shared databases
* staging data you need to preserve
* real user data
* production databases
* any database whose safety is uncertain

`migrate:fresh --seed` is only appropriate for a positively identified disposable local/test database.

Database migrations should normally be:

* additive
* backward-compatible where practical
* reversible
* safe for existing data

---

## Start Backend

The repository may use the included Windows PHP helper for Artisan commands.

For a local PHP development server, use the project's configured PHP environment.

Typical development endpoint:

```text
http://127.0.0.1:8000
```

Verify Laravel routes with:

```bash
.\php-local.bat artisan route:list
```

---

## Frontend Setup

Navigate to:

```bash
cd apps/web
```

Install dependencies:

```bash
npm install
```

Start development server:

```bash
npm run dev
```

Typical Vite development URL:

```text
http://localhost:5173
```

---

## Frontend Verification

Lint:

```bash
npm.cmd run lint
```

Production build:

```bash
npm.cmd run build
```

A successful build does not by itself prove that a page is production-ready.

For page-level verification, use the repository's:

```text
SKILL.md
```

audit protocol.

---

## Admin Panel

The administrative interface is part of the React application.

Typical local login route:

```text
http://localhost:5173/apanel/login
```

Default seeded development account may exist in local seeded environments.

Example development seed:

```text
Email: apanel@bstu.uz
Role:  apanel
```

Any default seeded password is strictly for local development/testing.

## Security Requirement

Never deploy production with a known/default seeded administrator password.

Production administrator credentials must be changed and managed securely.

Do not expose real credentials in documentation, source code, screenshots, logs, or audit reports.

---

## Dynamic Content & CMS

Public CMS-managed content should normally be stored in MySQL and exposed through Laravel APIs.

The runtime content path should generally be:

```text
Admin CMS
   ↓
Laravel API
   ↓
MySQL
   ↓
Public API
   ↓
React
```

Static frontend data should not be used as a hidden runtime fallback for database-managed content unless explicitly required by the architecture.

---

## Localization Details

Active locale configuration is stored in the database.

The `locales` table controls information such as:

* locale code
* language name
* active/inactive status
* direction

Locale management is available through the administrative system.

General UI translations are retrieved through APIs such as:

```text
GET /api/v1/translations?locale=...
```

Some CMS resources may also use dedicated translation tables.

Always inspect the actual resource architecture instead of assuming every translated field comes from the global translation endpoint.

---

## RTL / LTR

Arabic is normally configured as:

```text
RTL
```

English, Uzbek, and Russian normally use:

```text
LTR
```

Frontend locale handling is responsible for setting appropriate document attributes such as:

```html
<html lang="..." dir="...">
```

Responsive and localization audits should verify RTL/LTR behavior in the real browser, not only from source code.

---

## Media & Storage

Laravel-managed uploads use the public disk.

The public storage path:

```text
apps/api/public/storage
```

is a Laravel public link/junction to:

```text
storage/app/public
```

It is not intended to be a second independent upload directory.

Use the project's existing media/storage URL helpers.

Do not hardcode local filesystem paths into frontend source.

---

## Content Source of Truth

The following should be treated as the production runtime source of truth where applicable:

```text
MySQL
+
Laravel APIs
+
CMS/Admin management
```

Historical/static frontend data files are not automatically production runtime sources.

Legacy import/reference files should remain reference/migration data unless current application architecture explicitly uses them.

---

## Caching

Public CMS caching may use:

```text
public_content_cache_version
```

Private student and Admin responses should not be cached as public content.

Relevant Laravel cache-clearing commands may include:

```bash
.\php-local.bat artisan cache:clear
```

and:

```bash
.\php-local.bat artisan optimize:clear
```

Do not clear caches unnecessarily.

---

## Production Requirements

Before production deployment verify at minimum:

## Laravel

```text
APP_ENV=production
APP_DEBUG=false
```

Production configuration should also define appropriate:

* application URL
* frontend URL
* database credentials
* CORS origins
* Sanctum stateful domains
* token/session settings
* secure cookies
* HTTPS behavior
* mail settings
* cache store
* queue store
* log configuration
* storage configuration

Local development `.env` settings are not themselves production configuration.

Do not simply change the developer's local environment to production mode for testing.

---

## Production Verification

Before considering a public page production-ready, verify more than:

```text
HTTP 200
```

and more than:

```text
npm run build
```

Page-level production verification should include, where applicable:

* browser runtime
* console errors
* network failures
* API contracts
* responsive rendering
* localization
* RTL
* database relationships
* Admin/CMS management
* authentication/authorization
* media
* SEO
* production configuration

Use:

```text
SKILL.md
```

for the complete protocol.

---

## Git Safety

Before completing significant work:

```bash
git status
```

and:

```bash
git diff
```

should be reviewed.

Distinguish:

* changes created during the current task
* pre-existing developer/user changes

Do not silently revert unrelated work.

---

## Package Management

Do not manually edit:

```text
package-lock.json
```

or:

```text
node_modules/.package-lock.json
```

npm lockfile version `3` is expected.

Use npm for dependency changes.

Avoid permanent dependencies created solely for one disposable audit when a non-invasive method is available.

---

## Temporary Agent Files

Scratch folders such as:

```text
.agents/
.kilo/
```

are ignored by Git and should not become project source-of-truth.

Temporary:

* screenshots
* debug scripts
* audit values
* browser helpers
* diagnostic files

should not be committed unless they intentionally become part of the project's testing infrastructure.

Long-lived project guidance belongs in:

```text
README.md
AGENTS.md
SKILL.md
```

or appropriate source/data documentation.

---

## Development Principles

1. Preserve the existing monorepo architecture.
2. Keep React in `apps/web`.
3. Keep Laravel in `apps/api`.
4. Treat database/API-backed CMS content as runtime source-of-truth.
5. Use additive database migrations.
6. Protect existing data.
7. Preserve authentication and authorization.
8. Do not hide runtime/type/lint problems.
9. Prefer root-cause fixes.
10. Test changes after implementation.
11. Verify browser behavior for UI changes.
12. Check shared-component regression impact.
13. Keep production configuration environment-driven.
14. Never commit secrets.
15. Review Git changes before finishing.

---

## Documentation Responsibilities

Use:

### README Documentation

For:

* project overview
* setup instructions
* developer workflow
* architecture overview
* deployment basics

### AGENTS Documentation

For:

* repository-specific coding-agent behavior
* architecture constraints
* agent safety rules
* implementation conventions

### SKILL Documentation Responsibilities

For:

* page/route auditing
* end-to-end runtime verification
* repair workflow
* production-readiness decisions

This separation should be preserved as the project evolves.

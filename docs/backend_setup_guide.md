# Backend Setup & Configuration Guide

This document describes how to configure, run, and test the Laravel 13.19.0 REST API backend for the Bukhara State Technical University (BSTU) International Website.

The backend lives in `apps/api` and requires PHP `^8.3`, Laravel `^13.8`, Laravel Sanctum `^4.3`, and MySQL.

## Local Configuration

- **Database Name**: `bstu_international`
- **Database User**: `root`
- **Database Password**: (empty / no password)
- **Local IP / Host**: `127.0.0.1` (or `localhost`)
- **phpMyAdmin**: Accessible at [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/)

## Setup & Running the Application

### 1. Create the Database

Create the database using phpMyAdmin or the mysql CLI:

- Open phpMyAdmin: `http://localhost/phpmyadmin/`
- Create a new database named `bstu_international` with collation `utf8mb4_unicode_ci`.

### 2. Install Dependencies

Run the composer install command inside `apps/api`:

```bash
cd apps/api
composer install --prefer-dist
```

- (If PHP extensions like `zip` or `fileinfo` are disabled in your system, prefix composer commands using PHP flags, for example: `php -d extension=fileinfo -d extension=zip composer install --prefer-dist`.)*

### 3. Configure Environment Files

Generate your `.env` from `.env.example` and set up the MySQL parameters:

```env
APP_NAME="BSTU International"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bstu_international
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Generate App Key

```bash
php artisan key:generate
```

### 5. Database Migrations

Run the migrations to create all required tables:

```bash
php artisan migrate
```

- (Or `php -d extension=pdo_mysql artisan migrate` if extension is disabled globally)*

### 6. Seeding Database

Seed the database with all default roles, base permissions, and the default admin user:

```bash
php artisan db:seed
```

For a full local reset, use this only when you are comfortable deleting local data:

```bash
php artisan migrate:fresh --seed
```

The normal seed pipeline includes roles/permissions, apanel user, locales, translation keys/values, student-system translation keys, menus, pages, faculties, departments, programs, courses, news, blog posts, announcements, staff, services, videos, media metadata, and public settings. Green Campus content is managed through `/apanel/cms/green-campus`.

Default Admin User credentials:

- **Email**: `apanel@bstu.uz`
- **Password**: `password`
- **Role**: `apanel` (Full Control)

---

## Middlewares & Roles Architecture

### Custom Middlewares Registered

- **`LocaleMiddleware` (Global)**: Inspects the `Accept-Language` request header or `lang` query parameter and validates it against active database locales before configuring the system locale.
- **`role`**: Check if user has specified roles (e.g. `role:admin` or `role:admin|super_admin`). The `apanel` role bypasses all checks.
- **`permission`**: Check if user has specified permissions (e.g. `permission:manage-users`). The `apanel` role bypasses all checks.

### Seeded Roles (14 total)

- `apanel` (Admin Panel Full Control)
- `super_admin` (Super Administrator)
- `admin` (Administrator)
- `admission_officer` (Admission Officer)
- `international_office_staff` (International Office Staff)
- `call_center_staff` (Call Center Staff)
- `faculty_staff` (Faculty Staff)
- `department_staff` (Department Staff)
- `registrar_office_staff` (Registrar Office Staff)
- `dormitory_manager` (Dormitory Manager)
- `teacher` (University Teacher)
- `student` (University Student)
- `finance_staff` (Finance Staff)
- `document_officer` (Document Officer)

---

## API Documentation & Verification

### Authentication Endpoints (Prefix `/api/v1`)

| Method | Endpoint | Description | Auth Required |
|--------|-----------------------|---------------|
| `POST` | `/api/v1/auth/register` | Register a new user | No |
| `POST` | `/api/v1/auth/login` | Login and return bearer token & user details | No |
| `POST` | `/api/v1/auth/forgot-password` | Request password reset email | No |
| `POST` | `/api/v1/auth/reset-password` | Perform password reset | No |
| `GET`  | `/api/v1/auth/user` | Fetch current user & roles details | Yes (Bearer) |
| `POST` | `/api/v1/auth/logout` | Revoke the authenticated token | Yes (Bearer) |

### Testing the API

To view all registered routes in Laravel, run:

```bash
php artisan route:list
```

On this Windows workspace, `php-local.bat` is available and can be used when PHP extensions need to be explicitly enabled:

```bash
.\php-local.bat artisan route:list
```

To serve Laravel locally on port 8000:

```bash
php artisan serve --port=8000
```

The API is available at: `http://127.0.0.1:8000/api/v1`

### Release Verification

Before handing the backend to another developer or deploying, run:

```bash
.\php-local.bat -v
.\php-local.bat -m
.\php-local.bat artisan optimize:clear
.\php-local.bat artisan about
.\php-local.bat artisan route:list
.\php-local.bat artisan migrate:status
.\php-local.bat artisan test
composer validate
composer audit
npm.cmd audit --audit-level=high
.\vendor\bin\pint --test
```

Testing uses SQLite in-memory by default from `phpunit.xml`. Keep database-dependent feature tests deterministic; use manual smoke tests against the local MySQL database for seeded public CMS content.

## Storage, Sanctum, And CORS Notes

- Laravel Sanctum protects `/api/v1/auth/user`, `/api/v1/auth/logout`, student routes, and `/api/v1/apanel/*`.
- File uploads are stored on the public disk and exposed through Laravel storage routes. See `storage-docs/README.md`.
- Frontend development should point `VITE_API_BASE_URL` to `http://127.0.0.1:8000/api/v1`.
- If browser requests fail, confirm `APP_URL`, CORS allowed origins, and Sanctum stateful domain settings in `.env` / config.

## Production Readiness Settings

Use `.env.example` as a placeholder template only. Never copy secrets from local `.env` into documentation.

Recommended production values:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.example.edu
FRONTEND_URL=https://international.example.edu
CORS_ALLOWED_ORIGINS=https://international.example.edu
SANCTUM_STATEFUL_DOMAINS=international.example.edu
SANCTUM_TOKEN_EXPIRATION_MINUTES=1440
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
LOG_CHANNEL=daily
LOG_LEVEL=warning
CACHE_STORE=redis
QUEUE_CONNECTION=redis
PUBLIC_API_CACHE_TTL=600
FILESYSTEM_DISK=public
```

Public CMS cache is versioned by the `public_content_cache_version` cache key. Apanel writes to public resources rotate the version automatically. For deployment or manual database repair, clear runtime state:

```bash
.\php-local.bat artisan cache:clear
.\php-local.bat artisan optimize:clear
```

Upload constraints:

- Student application documents: PDF/JPEG/PNG/WebP, max 10 MB.
- Apanel media: PDF/JPEG/PNG/WebP/MP4/AVI/MOV, max 200 MB.
- Uploaded application documents remain behind authenticated student/apanel download routes.

## Common Errors

- `SQLSTATE[HY000] [2002]`: MySQL is not reachable. Start MySQL and verify `DB_HOST`, `DB_PORT`, and database name.
- `could not find driver`: PHP is missing `pdo_mysql`; verify with `.\php-local.bat -m` and update `php.ini`.
- `public/storage NOT LINKED`: run `.\php-local.bat artisan storage:link`.
- Browser CORS blocked: set `CORS_ALLOWED_ORIGINS` to the exact frontend origin and keep credentials enabled only for trusted origins.
- Sanctum 401 with a token: ensure the request sends `Authorization: Bearer <token>` and that `SANCTUM_TOKEN_EXPIRATION_MINUTES` has not expired the token.
- Stale config/routes/views: run `.\php-local.bat artisan optimize:clear`.

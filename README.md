# Bukhara State Technical University (BSTU) International Website Monorepo

This repository is structured as a unified monorepo containing the web frontend, backend API, mobile application, and related documentation.

---

## 📁 Folder Structure

- **`apps/web`**: React 19 + Vite + Tailwind CSS 4 web application (includes student dashboard and `apanel` admin management).
- **`apps/api`**: Laravel + PHP REST API backend.
- **`apps/mobile`**: Flutter mobile application.
- **`docs`**: System architecture, API definitions, student system guides, and mobile guides.
- **`database-docs`**: Database schemas, entity-relationship diagrams, and migrations info.
- **`storage-docs`**: File storage configurations and guides.

---

## 🛠️ Technology Stack

- **Web Frontend**: React 19, React Router 7, Framer Motion, Swiper, Lucide React
- **CSS Engine**: Tailwind CSS 4 (via PostCSS)
- **Backend Service**: Laravel REST API (PHP 8.3+)
- **Database Engine**: MySQL (InnoDB)
- **Mobile Platform**: Flutter SDK (Dart)
- **Authentication**: Laravel Sanctum (Token-based)
- **State Management**: React Context (Web), ChangeNotifier / InheritedWidget (Mobile)

---

## 📚 Documentation Index

- **[Architecture](docs/system_architecture.md)** — high-level web, API, mobile, and MySQL architecture.
- **[API](docs/api_endpoints_specification.md)** — public, student, and apanel REST API endpoints.
- **[API Backend Notes](docs/api_backend_development.md)** — Laravel API conventions, checks, production notes, and maintenance commands.
- **[Backend Setup](docs/backend_setup_guide.md)** — Laravel setup, PHP extension notes, and local API checks.
- **[Web Frontend Notes](docs/web_frontend_development.md)** — React/Vite commands, environment, routing, assets, and production notes.
- **[Frontend/API Integration](docs/frontend_api_integration.md)** — React service and localization integration.
- **[Apanel](docs/admin_panel_guide.md)** — `/apanel/` routes, resources, permissions, and workflows.
- **[Student System](docs/student_system_guide.md)** — `/student/` admissions, documents, contracts, payments, and support.
- **[Mobile App](docs/mobile_app_guide.md)** — Flutter app structure and API integration.
- **[Database Schema](database-docs/database_schema.md)** — MySQL schema reference.
- **[Data Migration](database-docs/database_migration_guide.md)** — static content extraction and Laravel seeding process.
- **[Storage](storage-docs/laravel_storage_guide.md)** — Laravel storage, public symlink, and file URL resolution.
- **[Laravel Release Notes](docs/laravel_release_notes.md)** — upstream Laravel application release notes retained for reference.

Historical source captures used during cleanup have been merged into durable data files, seed JSON, and active documentation.

---

## 🗄️ Local Database Settings

To configure the local database connection:

- **Connection Type**: `mysql`
- **Host**: `127.0.0.1`
- **Port**: `3306`
- **Database Name**: `bstu_international`
- **Username**: `root`
- **Password**: *Empty (Default)*
- **phpMyAdmin**: Available locally at [http://localhost/phpmyadmin/](http://localhost/phpmyadmin/)

---

## 🚀 How to Run the System

### 1. Backend Server (`apps/api`)

1. **Navigate to backend**:

   ```bash
   cd apps/api
   ```

2. **Install dependencies**:

   ```bash
   composer install
   ```

3. **Configure environment**:
   Copy `.env.example` to `.env` and set MySQL credentials.
4. **Generate Application Key**:

   ```bash
   php artisan key:generate
   ```

5. **Migrate and Seed**:
   Run with required extensions enabled (especially `pdo_mysql` and `fileinfo` on Windows):

   ```bash
   php -d extension=fileinfo -d extension=zip -d extension=pdo_mysql artisan migrate --seed
   ```

   Use `migrate:fresh --seed` only for disposable local databases because it deletes existing data.

6. **Start Dev Server**:
   Start using the built-in PHP server to pass CLI extensions properly:

   ```bash
   php -d extension=fileinfo -d extension=zip -d extension=pdo_mysql -S 127.0.0.1:8000 -t public
   ```

7. **Verify routes**:

   ```bash
   .\php-local.bat artisan route:list
   ```

### 2. Web Frontend (`apps/web`)

1. **Navigate to frontend**:

   ```bash
   cd apps/web
   ```

2. **Install dependencies**:

   ```bash
   npm install
   ```

3. **Start Development Server**:

   ```bash
   npm run dev
   ```

4. **Build Production Bundle**:

   ```bash
   npm run build
   ```

### 3. Flutter Mobile App (`apps/mobile`)

1. **Navigate to mobile**:

   ```bash
   cd apps/mobile
   ```

2. **Install dependencies**:

   ```bash
   flutter pub get
   ```

3. **Analyze Code Integrity**:

   ```bash
   flutter analyze
   ```

4. **Run on Target Device / Emulator**:

   ```bash
   flutter run
   ```

---

## 🔒 Admin Panel (`apanel`) Credentials

The admin panel resides in the React SPA at `/apanel`. Use the default seeded credentials to log in:

- **URL**: `http://localhost:5173/apanel/login` (or equivalent dev port)
- **Email**: `apanel@bstu.uz`
- **Password**: `password`
- **Role**: `apanel` (Provides full administrative CRUD control and approval over all registrations, pages, translations, and contracts).

---

## 🌍 Dynamic Languages & RTL/LTR

- **Seeded defaults**: English (`en`), Uzbek (`uz`), Russian (`ru`), and Arabic (`ar`).
- **Source of truth**: Active languages, names, and text directions come from the `locales` table and are managed from `/apanel/locales`.
- **Dynamic localized loading**: Visible website, student, mobile, and apanel text is resolved from database translation records through `GET /api/v1/translations?locale=...`.
- **RTL/LTR support**: Arabic is seeded as RTL; other active locales use their database `direction` value.

---

## 🧹 Temporary Files

Agent scratch folders such as `.agents/` and `.kilo/` are ignored by Git. Long-lived setup, architecture, API, database, storage, and mobile notes belong in `docs/`, `database-docs/`, or `storage-docs/` rather than root task logs.

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

- **[Architecture](docs/ARCHITECTURE.md)** — high-level web, API, mobile, and MySQL architecture.
- **[API](docs/API.md)** — public, student, and apanel REST API endpoints.
- **[API Backend Notes](docs/API_BACKEND_README.md)** — Laravel API conventions, checks, production notes, and maintenance commands.
- **[Backend Setup](docs/BACKEND_SETUP.md)** — Laravel setup, PHP extension notes, and local API checks.
- **[Web Frontend Notes](docs/WEB_FRONTEND_README.md)** — React/Vite commands, environment, routing, assets, and production notes.
- **[Frontend/API Integration](docs/FRONTEND_API_INTEGRATION.md)** — React service and localization integration.
- **[Apanel](docs/APANEL.md)** — admin panel routes, resources, permissions, and workflows.
- **[Student System](docs/STUDENT_SYSTEM.md)** — student admissions, documents, contracts, payments, and support.
- **[Mobile App](docs/MOBILE_APP.md)** — Flutter app structure and API integration.
- **[Database Schema](database-docs/SCHEMA.md)** — MySQL schema reference.
- **[Data Migration](database-docs/DATA_MIGRATION.md)** — static content extraction and Laravel seeding process.
- **[Storage](storage-docs/README.md)** — Laravel storage, public symlink, and file URL resolution.
- **[Laravel Release Notes](docs/LARAVEL_RELEASE_NOTES.md)** — upstream Laravel application release notes retained for reference.

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

5. **Fresh Migrate and Seed**:
   Run with required extensions enabled (especially `pdo_mysql` and `fileinfo` on Windows):

   ```bash
   php -d extension=fileinfo -d extension=zip -d extension=pdo_mysql artisan migrate:fresh --seed
   ```

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

## 🌍 Supported Languages & RTL/LTR

- **Supported Languages**: English (`en`), Uzbek (`uz`), Russian (`ru`), Arabic (`ar`).
- **Dynamic Localized Loading**: All visible website and mobile text is database-driven and resolved through translation keys (`GET /api/v1/translations?locale=...`).
- **RTL Support**: Arabic (`ar`) renders in RTL mode automatically. This shifts alignments, sidebar placement, grids, input directions, and buttons across the React frontend and Flutter app natively.
- **LTR Support**: English, Uzbek, and Russian render in LTR mode.

---

## 🧹 Temporary Files

Agent scratch folders such as `.agents/` and `.kilo/` are ignored by Git. Long-lived setup, architecture, API, database, storage, and mobile notes belong in `docs/`, `database-docs/`, or `storage-docs/` rather than root task logs.

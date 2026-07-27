# Project Milestones & System Architecture

## Monorepo Layout

- **React Frontend**: Located at `apps/web`. Uses React 19, Vite, Tailwind CSS 4, and dynamic API client services.
- **Laravel Backend API**: Located at `apps/api`. Uses Laravel 13, MySQL, and Sanctum tokens for authentication.
- **Flutter Mobile App**: Located at `apps/mobile` (interacts with the same API endpoints).

## Project Checklist Status

- [x] **Monorepo Structure Setup**: Configured directories and workspaces.
- [x] **Laravel Backend Base**: API route scaffolding, migrations, and model layers.
- [x] **Multilingual Database Schema**: Support for dynamic localized content via translation tables (locales, translation_keys, and translation_values).
- [x] **REST API Endpoints**: Public routes for faculties, departments, news, blogs, and settings.
- [x] **React Content Migration**: Transferred static HTML/React texts to MySQL seeders and data files.
- [x] **Client-API Connection**: Replaced mock data calls in React with dynamic service fetch calls.
- [x] **Admin Control Panel (apanel)**: Professional dashboards for managing site modules.
- [x] **Student Admissions System**: Online application forms (`/apply`), contract status, and document submissions.

# BSTU International — Frontend API Integration Guide

This guide details how the React 19 frontend (`apps/web`) connects to the Laravel 13.19.0 REST API (`apps/api`) and dynamic MySQL database translation key system.

---

## 1. Environment Configuration

The application uses Vite's environment system. In `apps/web/`, two files define API coordinates:

* `.env` (local development configurations)
* `.env.example` (template file)

### Settings

```env
VITE_API_BASE_URL=http://127.0.0.1:8000/api/v1
VITE_DEFAULT_LOCALE=en
VITE_SUPPORTED_LOCALES=en,uz,ru,ar
```

---

## 2. API Client & Session Manager

* **API Client (`apps/web/src/lib/api.js`):** Lightweight client using native `fetch`.
  * Automatically appends `Authorization: Bearer <token>` to headers if a user session is active.
  * Detects `FormData` payloads to omit default JSON headers, allowing multipart file uploads.
  * Injects the active locale into query parameters (`?locale=...`) for all requests.
* **Token Manager (`apps/web/src/lib/auth.js`):** Interacts with localStorage to store and verify active session JWT tokens.
* **Fail-Safe LocalStorage (`apps/web/src/lib/storage.js`):** Wraps storage access to prevent crashes in private browser windows.

---

## 3. Global State Providers (Contexts)

Three React context layers manage global state across the site:

### A. LocaleContext (`apps/web/src/context/LocaleContext.jsx`)

* **Purpose:** Manages the selected language, website settings, menu structures, and loads translations directly from the API database.
* **Translations:** Dynamically fetches translations from `GET /api/v1/translations?locale={lang}` and stores the returned dictionary.
* **RTL/LTR Switcher:** Automatically sets `document.documentElement.dir` and `document.body.dir` to `rtl` for Arabic (`ar`), and `ltr` for English, Uzbek, and Russian.
* **Compatibility:** Exposes `t(keyPath, defaultText)` which resolves dot-notation keys from the API dictionary. If a key is absent, it uses the caller-provided fallback text, then the key string.

### B. AuthContext (`apps/web/src/context/AuthContext.jsx`)

* **Purpose:** Orchestrates user sessions (login, registration, student profile updates, and logouts).
* **Session Persistence:** Verifies authorization token status on application mount and restores the user object dynamically.

### C. AppDataContext (`apps/web/src/context/AppDataContext.jsx`)

* **Purpose:** Performs batched cached API calls to retrieve list models when the selected language changes.
* **Cached Entities:**
  * Faculties
  * Departments
  * Programs
  * Services
  * Promo Videos
  * Green Campus stats & articles

---

## 4. Reusable API UI Components

Located under `apps/web/src/components/common/`, these components standardise user feedback during asynchronous events:

* `LoadingState.jsx`: Premium spinner overlay with customized message text.
* `ErrorState.jsx`: Standardized card representing fetch failures with reload capability.
* `EmptyState.jsx`: Clean state representation for empty collections.
* `FormError.jsx` / `FieldError.jsx`: Form alerts and individual validation checks displaying backend response validation errors.

---

## 5. Service Endpoint Mappings

All API interactions are separated into modular service files located in `apps/web/src/services/`:

| Service Class | Endpoint Path | HTTP Method | Description |
| :--- | :--- | :--- | :--- |
| `translationService` | `/translations` | `GET` | Fetches dictionary key-value translations |
| `menuService` | `/menus` | `GET` | Fetches dynamic navigation headers/footers |
| `pageBlockService` | `/page-blocks` | `GET` | Fetches page-specific block content (Hero, Rector, etc.) |
| `newsService` | `/news` | `GET` | Fetches news articles list |
| `newsService` | `/news/{slug}` | `GET` | Fetches individual news article details |
| `announcementService` | `/announcements` | `GET` | Fetches announcements list |
| `announcementService` | `/announcements/{slug}` | `GET` | Fetches announcement details |
| `facultyService` | `/faculties` | `GET` | Fetches university faculties |
| `facultyService` | `/faculties/{slug}` | `GET` | Fetches individual faculty details |
| `programService` | `/programs` | `GET` | Fetches academic programs list |
| `programService` | `/programs/{slug}` | `GET` | Fetches program detail pages |
| `studentService` | `/student/profile` | `GET` | Fetches student profile data |
| `studentService` | `/student/profile` | `PUT` | Updates student profile details |
| `applicationService` | `/applications` | `GET` | Fetches authenticated student applications |
| `applicationService` | `/applications` | `POST` | Creates a new academic application |
| `applicationService` | `/applications/{id}/submit` | `POST` | Finalizes and submits an application draft |
| `studentService` | `/student/notifications` | `GET` | Fetches authenticated student system alerts |
| `studentService` | `/student/contracts` | `GET` | Fetches student contracts |
| `studentService` | `/student/payments` | `GET` | Fetches student payments |
| `studentService` | `/student/support-tickets` | `GET/POST` | Lists and creates support tickets |
| `studentService` | `/student/support-tickets/{id}/messages` | `POST` | Adds support ticket messages |
| `commentService` | `/comments` | `POST` | Submits a blog or news comment |
| `inquiryService` | `/inquiries` | `POST` | Submits a public contact form inquiry |

---

## 6. Page Routing & Performance

Pages are lazy-loaded within `apps/web/src/App.jsx` using `React.lazy()` and wrapped in a `<Suspense>` boundary to optimize bundle size and Cumulative Layout Shift (CLS).

### Protected Student Portal Routes

Student dashboard pages are protected using the `<StudentRoute>` route wrapper, which checks user authentication and redirects unauthenticated users to `/student/login`.

```jsx
<Route 
  path="/student/profile" 
  element={
    <StudentRoute>
      <StudentLayout>
        <StudentProfile />
      </StudentLayout>
    </StudentRoute>
  } 
/>
```

Apanel routes use `<AdminRoute>`, which redirects unauthenticated users to `/apanel/login` and verifies the `apanel` role before rendering dashboard or CRUD pages.

Production public pages load content from the Laravel API/MySQL system. Legacy
React content captures are quarantined under
`scripts/import-react-content/legacy-react-data/` for the manual import utility
only; they are not imported by runtime public routes. `apps/web/src/data` keeps
`translations.js` only as a technical fallback if the translations API is
unavailable during local development.

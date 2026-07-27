# BSTU International System — REST API Specification

All API endpoints are prefixed with `/api/v1`. Consistent JSON formats are returned for both success and error responses.

---

## 1. Authentication & Security

### Admin Panel Credentials

- **Email:** `apanel@bstu.uz`
- **Password:** `password`
- **Role:** `apanel`

### Using Bearer Tokens

Endpoints protected by the Sanctum middleware expect the token in the `Authorization` header:

```http
Authorization: Bearer <your_access_token_here>
```

### Protection Model

- Public content routes are open but rate limited.
- Student routes require `auth:sanctum` and always scope applications, documents, contracts, payments, notifications, and tickets to the authenticated user.
- Apanel routes require both `auth:sanctum` and `role:apanel`; dynamic CRUD is restricted to the backend whitelist.
- Password reset uses Laravel's password broker token verification.
- API exceptions return JSON without stack traces in production. Keep `APP_DEBUG=false` outside local development.

### Rate Limits

- Login: `5/minute` per email/IP.
- Register: `3/minute` per IP.
- Forgot/reset password: `3/minute` per email/IP.
- Public API: `120/minute` per IP.
- Student API: `90/minute` per authenticated user.
- Apanel API: `120/minute` per authenticated user.
- Upload endpoints: `20/minute` per authenticated user.

### Public Cache

Public CMS reads are cached for `PUBLIC_API_CACHE_TTL` seconds. Cached resources include locales, translations, settings, menus, home content, pages, faculties, departments, and programs. Private student/apanel responses are not cached.

Clear cache after manual data repairs:

```bash
.\php-local.bat artisan cache:clear
.\php-local.bat artisan optimize:clear
```

---

## 2. Localization Support

### Query Parameter or Headers

Every public endpoint and student dashboard query accepts:

- Query Parameter: `?locale=en` (supports `en`, `uz`, `ru`, `ar`)
- Alternatively, via the HTTP request header: `Accept-Language: uz`
- If neither is provided, the API defaults to English (`en`).

### Response Metadata

Most localized content endpoints inject `locale` and `direction` metadata into the response envelope. The translations endpoint returns `{ locale, direction, data }` directly:

```json
{
  "locale": "ar",
  "direction": "rtl",
  "data": {
    "id": 1,
    "slug": "faculty-of-technology",
    "name": "كلية التكنولوجيا"
  }
}
```

- Layout Directions:*

- `en`, `uz`, `ru` = `ltr`
- `ar` = `rtl`

---

## 3. Public API Endpoints

### Localization

- **`GET /api/v1/locales`** — List all active system languages.
- **`GET /api/v1/translations?locale=en`** — Get dynamic key-value dictionary for UI localization.

### Structure & Content

- **`GET /api/v1/settings`** — Fetch public key-value settings.
- **`GET /api/v1/settings/public`** — Backward-compatible alias for public key-value settings.
- **`GET /api/v1/menus?locale=en`** — Fetch all active menus grouped by location.
- **`GET /api/v1/menus/{location}?locale=en`** — Fetch nested navigation links (e.g. `location=header`).
- **`GET /api/v1/home?locale=en`** — Fetch the published home page and its active page blocks.
- **`GET /api/v1/pages?locale=en`** — List public pages.
- **`GET /api/v1/pages/{slug}?locale=en`** — View detailed page metadata and content.
- **`GET /api/v1/page-blocks/{pageSlug}?locale=en`** — Fetch all blocks of a page (e.g. hero cards, slider config).

### Academic

- **`GET /api/v1/faculties?locale=en`** — List active faculties.
- **`GET /api/v1/faculties/{slug}?locale=en`** — View faculty details.
- **`GET /api/v1/departments?locale=en`** — List departments.
- **`GET /api/v1/departments/{slug}?locale=en`** — View department details.
- **`GET /api/v1/programs?locale=en`** — List active study programs.
- **`GET /api/v1/programs/{slug}?locale=en`** — View study program details.
- **`GET /api/v1/courses?locale=en`** — List all courses.

### News & Announcements

- **`GET /api/v1/news?locale=en&page=1`** — Paginated list of news.
- **`GET /api/v1/news/{slug}?locale=en`** — View news detail (increments views count).
- **`GET /api/v1/blog?locale=en&page=1`** — Paginated blog posts stored in the `news` table with `category=blog`.
- **`GET /api/v1/blog/{slug}?locale=en`** — View blog post detail (increments views count).
- **`GET /api/v1/announcements?locale=en`** — List active announcements.
- **`GET /api/v1/announcements/{slug}?locale=en`** — View announcement details.

### Services & Media

- **`GET /api/v1/services?locale=en`** — List active university services.
- **`GET /api/v1/services/{slug}?locale=en`** — View service details.
- **`GET /api/v1/videos?locale=en`** — Fetch campus video configurations.
- **`GET /api/v1/staff?locale=en`** — List active leadership, faculty, and department staff profiles.
- **`GET /api/v1/staff/{slug}?locale=en`** — View one public staff or leadership profile.
- **`GET /api/v1/media/{id}`** — Fetch absolute URL and metadata for files.
- **`GET /api/v1/green-campus/stats?locale=en`** — Fetch Green Campus performance metrics.
- **`GET /api/v1/green-campus/articles?locale=en`** — List Green Campus articles and news.
- **`GET /api/v1/green-campus/articles/{slug}?locale=en`** — View specific Green Campus article (increments view count).

### Communication

- **`POST /api/v1/inquiries`** — Submit contact form inquiry.
- **`POST /api/v1/comments`** — Post comments on pages, news, or applications (supports anonymous or logged-in users).

---

## 4. Authentication Endpoints

- **`POST /api/v1/auth/register`** — Register new user.
- **`POST /api/v1/auth/login`** — Authenticate credentials, returns Bearer token.
- **`POST /api/v1/auth/forgot-password`** — Send password reset link.
- **`POST /api/v1/auth/reset-password`** — Reset account password.
- **`GET /api/v1/auth/user`** *(Protected)* — Get current user information.
- **`POST /api/v1/auth/logout`** *(Protected)* — Invalidate user token.

---

## 5. Student Portal Endpoints (Auth Required)

- These endpoints automatically scope data so that a student can only view/write their own records.*

- **`GET /api/v1/student/profile`** — Fetch student profile, guardian, and education background.
- **`PUT /api/v1/student/profile`** — Update student personal/passport/contact information (creates profile dynamically if not initialized).
- **`POST /api/v1/applications`** — Create a draft admission application.
- **`GET /api/v1/applications`** — List all applications submitted/drafted by the student.
- **`GET /api/v1/applications/{id}`** — View specific application details.
- **`PUT /api/v1/applications/{id}`** — Edit draft application details.
- **`POST /api/v1/applications/{id}/submit`** — Submit the application (locks edits, sets status to `'submitted'`).
- **`POST /api/v1/applications/{id}/documents`** — Upload document file for application.
- **`DELETE /api/v1/applications/{id}/documents/{documentId}`** — Delete a student's own draft document.
- **`GET /api/v1/student/documents/{id}/download`** — Download a student's own uploaded document.
- **`GET /api/v1/student/notifications`** — View student's system notifications.
- **`PATCH /api/v1/student/notifications/{notifId}/read`** — Mark one notification as read.
- **`POST /api/v1/student/notifications/read-all`** — Mark all notifications as read.
- **`GET /api/v1/student/contracts`** — List tuition fee contracts.
- **`GET /api/v1/student/payments`** — List tuition payment histories.
- **`GET /api/v1/student/document-requests`** — List visa support/transcript requests.
- **`GET /api/v1/student/support-tickets`** — List support tickets.
- **`POST /api/v1/student/support-tickets`** — Create a support ticket.
- **`POST /api/v1/student/support-tickets/{id}/messages`** — Add a message to a support ticket.

---

## 6. Admin Panel CRUD Endpoints (`/api/v1/apanel/*`)

- Protected by `auth:sanctum` and `role:apanel` middleware. Full CRUD is mapped via dynamic resource routes.*

### List Operations Parameters

- **Pagination:** `?page=1&per_page=15`
- **Searching:** `?search=query` (searches columns like slug, code, name, email, subject, text, title)
- **Sorting:** `?sort_by=field&sort_dir=desc|asc`
- **Filtering:** `?filter[is_active]=1` or `?status=pending`

### Whitelisted CRUD Resources

1. `locales`
2. `translation-keys`
3. `translation-values`
4. `settings`
5. `menus`
6. `menu-items`
7. `pages`
8. `page-blocks`
9. `faculties`
10. `departments`
11. `programs`
12. `courses`
13. `news`
14. `announcements`
15. `staff`
16. `services`
17. `videos`
18. `media` (handles file uploads, registers path, returns URL)
19. `users`
20. `roles`
21. `permissions`
22. `students`
23. `applications`
24. `application-documents`
25. `contracts`
26. `payments`
27. `inquiries`
28. `support-tickets`
29. `comments`
30. `notifications`
31. `green-campus-stats`
32. `green-campus-articles`
33. `audit-logs` (Read-only logs showing CRUD trails)
34. `application-status-histories`

### Specialized Apanel Operations

- **`GET /api/v1/apanel/application-documents/{id}/download`** — Secure document download for apanel users.

### Apanel API Usage Notes

- Authenticate through `POST /api/v1/auth/login`.
- Send the returned token as `Authorization: Bearer <token>`.
- Only resources listed above are available through dynamic CRUD.
- Public CMS writes rotate the public cache version so frontend reads refresh automatically.
- Use `GET /api/v1/apanel/{resource}?page=1&per_page=15` for paginated lists; `per_page` is capped server-side.
- Media uploads must use multipart form data with `file`, optional `title`, `alt_text`, `type`, `is_public`, and `alt_key`.

---

## 7. Example Requests & Responses

### 1. Authenticating Login

- **`POST /api/v1/auth/login`**
- **Request Body:**

```json
{
  "email": "apanel@bstu.uz",
  "password": "password"
}
```

- **Response:**

```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "Admin Panel User",
      "email": "apanel@bstu.uz"
    },
    "access_token": "4|igG16A8QrpTJ0...",
    "token_type": "Bearer"
  },
  "message": "User logged in successfully"
}
```

### 2. Fetching Localized Program Details (Arabic)

- **`GET /api/v1/programs/chemical-technology-of-oil-and-gas?locale=ar`**
- **Response:**

```json
{
  "locale": "ar",
  "direction": "rtl",
  "data": {
    "id": 1,
    "faculty_id": 1,
    "department_id": 1,
    "slug": "chemical-technology-of-oil-and-gas",
    "degree": "Bachelor",
    "tuition_fee": "3500.00",
    "name": "التكنولوجيا الكيميائية للنفط والغاز",
    "description": "دراسة شاملة للمعالجة البتروكيماوية، وتخليق الوقود، والهندسة البيئية في صناعات النفط والغاز."
  }
}
```

# BSTU International — Student Application System

## Overview

The Student Application System lets international students register, complete profile data, create an admission application, upload documents, track application status, receive notifications, and follow contracts/payments from the React student portal. The source of truth is Laravel API data stored in MySQL.

## Architecture

| Layer | Technology | Location |
|---|------|
| Frontend | React 19 + Vite + Tailwind CSS 4 | `apps/web/src/features/student/` |
| Backend API | Laravel 13.19.0 + Sanctum | `apps/api/app/Http/Controllers/Api/StudentApiController.php` |
| Database | MySQL `bstu_international` | Laravel Eloquent models and migrations |
| Auth | Laravel Sanctum token auth | `apps/api/app/Http/Controllers/Api/V1/AuthController.php` |

## Student Flow

1. Register at `/student/register`.
2. Log in at `/student/login`.
3. Complete profile at `/student/profile`, including phone, gender, birth date, passport number, passport expiry date, nationality, address, guardian info, and education background.
4. Create or edit an application draft at `/student/application`.
5. Select degree level, faculty, department, program, language of study, and study mode.
6. Upload required documents at `/student/documents`.
7. Submit the application from `/student/application`.
8. Track status at `/student/application/status`.
9. Read notifications at `/student/notifications`.
10. View contracts and payments at `/student/contracts` and `/student/payments`.
11. Contact admissions through `/student/support`.

## Frontend Routes

| Route | Purpose |
|------|
| `/student/login` | Student login |
| `/student/register` | Student registration |
| `/student/dashboard` | Profile, application, document, notification, contract, and payment summary |
| `/student/profile` | Student profile form |
| `/student/application` | Application draft/create/submit flow |
| `/student/application/status` | Status timeline and history |
| `/student/documents` | Required document checklist and upload/delete |
| `/student/notifications` | Notification inbox |
| `/student/contracts` | Contract list |
| `/student/payments` | Payment list |
| `/student/support` | Support tickets |

## Statuses

Application statuses: `draft`, `submitted`, `under_review`, `missing_documents`, `accepted`, `rejected`, `contract_pending`, `payment_pending`, `enrolled`, `active_student`, `graduated`.

Payment statuses: `pending`, `paid`, `partially_paid`, `rejected`, `cancelled`.

Document statuses: `pending`, `approved`, `rejected`, `requested`.

Every important application status change writes to `application_status_histories` with `application_id`, `old_status`, `new_status`, `status`, `comment`/`note`, `changed_by`, and timestamps.

## Document Workflow

Required document types: `passport`, `photo`, `education_certificate`, `transcript`, `medical_certificate`, `language_certificate`, `payment_receipt`, `other`.

Upload stores files on Laravel's `public` disk under `storage/app/public/documents/` and saves metadata in `application_documents`: `application_id`, `document_name`, `document_type`, `file_path`, `original_name`, `mime_type`, `size`, `status`, and `note`.

Students can delete their own documents only while the application is still `draft`. Student download is protected by `GET /api/v1/student/documents/{id}/download`, which verifies ownership. Apanel download is protected by `GET /api/v1/apanel/application-documents/{id}/download` and requires the `apanel` role.

## Apanel Review Flow

Apanel users log in at `/apanel/login` and review applications at `/apanel/applications` and `/apanel/applications/{id}`. The detail page supports profile review, guardian/education review, uploaded document review, document approve/reject/request actions, status changes with internal notes, status history, notifications, and contract/payment visibility.

The general apanel CRUD endpoints also manage contracts, payments, notifications, students, applications, and application documents. Application list filtering supports `status`, `program_id`, `faculty_id`, and `nationality` where available.

## Notifications

Notifications are created for application creation, submission, status changes, document uploads, document review changes, contract updates, and payment updates. Students can read one notification or mark all notifications as read.

Student-facing translation keys are maintained in `StudentSystemTranslationSeeder`, and `DatabaseSeeder` runs it during normal seeding so fresh databases include student, application, document, contract, payment, notification, support, auth, and Faculty of Technology UI labels.

## API Endpoints

| Method | Endpoint | Purpose |
|------|---|
| GET | `/api/v1/student/profile` | Get profile |
| PUT | `/api/v1/student/profile` | Create/update profile |
| POST | `/api/v1/applications` | Create draft application |
| GET | `/api/v1/applications` | List own applications |
| GET | `/api/v1/applications/{id}` | View own application |
| PUT | `/api/v1/applications/{id}` | Update draft application |
| POST | `/api/v1/applications/{id}/submit` | Submit draft |
| POST | `/api/v1/applications/{id}/documents` | Upload document |
| DELETE | `/api/v1/applications/{id}/documents/{docId}` | Delete own draft document |
| GET | `/api/v1/student/documents/{id}/download` | Secure student document download |
| GET | `/api/v1/student/notifications` | List notifications |
| PATCH | `/api/v1/student/notifications/{id}/read` | Mark one read |
| POST | `/api/v1/student/notifications/read-all` | Mark all read |
| GET | `/api/v1/student/contracts` | List contracts |
| GET | `/api/v1/student/payments` | List payments |
| GET | `/api/v1/student/support-tickets` | List support tickets |
| POST | `/api/v1/student/support-tickets` | Create support ticket |
| POST | `/api/v1/student/support-tickets/{id}/messages` | Add ticket message |
| GET | `/api/v1/apanel/application-documents/{id}/download` | Secure apanel document download |
| GET/POST/PUT/DELETE | `/api/v1/apanel/{resource}` | Apanel CRUD resources |

## Security Rules

Student private routes require Sanctum auth in React and Laravel. Student API queries scope profile, application, document, contract, payment, notification, and support data to the authenticated user. Apanel routes require both Sanctum auth and the `apanel` role. Protected frontend routes wait for auth checks before rendering private views and handle unauthenticated users by redirecting to the correct login page.

## Translation And RTL

Student pages use `useLanguage()` / `t()` where practical, with translation values loaded from MySQL through Laravel. Supported locales are `en`, `uz`, `ru`, and `ar`. Arabic sets `document.documentElement.dir = rtl`; English, Uzbek, and Russian use LTR.

## Known Limitations

Public website and student-facing content should be served by the Laravel API/MySQL system. `apps/web/src/data` is no longer used as a runtime fallback content source.

Full browser E2E automation is not currently committed in the repo. Verification is done through route checks, migration checks, lint/build, and manual API/browser testing.

## Verification

Run backend checks:

```bash
cd apps/api
.\php-local.bat artisan migrate
.\php-local.bat artisan route:list
```

Run frontend checks:

```bash
cd apps/web
npm.cmd run lint
npm.cmd run build
```

Run mobile checks when Flutter SDK is installed:

```bash
cd apps/mobile
flutter analyze
```

Default apanel user:

```text
email: apanel@bstu.uz
password: password
role: apanel
```

Last updated: 2026-07-12

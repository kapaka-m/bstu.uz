# BSTU Admin Control Panel (apanel) Developer Guide

The **BSTU Admin Control Panel (apanel)** is a secure, multilingual management dashboard integrated directly into the Bukhara State Technical University website frontend. It communicates with the Laravel REST API whitelisted endpoints to control public site contents, dynamic locales, dictionary translations, and student applications.

---

## 1. Overview & Credentials

* **Panel URL Path:** `/apanel`
* **Admissions Login URL:** `/apanel/login`
* **Role Access:** Restricted to accounts holding the `apanel` role or equivalent permissions.
* **Default Seed Credentials:**
  * **Email:** `apanel@bstu.uz`
  * **Password:** `password`
  * **Permissions:** Full control over all system assets, settings, and tables.

---

## 2. Directory Layout & Architecture

The admin panel is grouped under the features folder in `apps/web/src/features/apanel/` to maintain modular separation:

* `layouts/ApanelLayout.jsx` — Container displaying sidebar links, breadcrumbs, locales, and logout triggers.
* `components/DataTable.jsx` — Listing table with headers, sorting, status toggles, and view/edit/delete actions.
* `components/FormBuilder.jsx` — Generic builder mapping whitelisted form inputs, including text, number, select dropdowns, and media pickers.
* `components/TranslationTabs.jsx` — Tab switcher displaying completeness checklists for locales (`en`, `uz`, `ru`, `ar`).
* `components/MediaPicker.jsx` — Embedded overlay picker connecting directly to `/api/v1/apanel/media` to select upload paths.
* `pages/ApanelCrud.jsx` — Generic resource CRUD controller mapping route slugs to schema configurations.
* `pages/ApanelApplicationDetail.jsx` — Specialized admissions verification console for student profiles, contracts, and uploaded documents.

---

## 3. Supported Resources & Routes

Below is the complete route mapping for all whitelisted CRUD entities:

| Route Path | CRUD Resource Slug | Mapped API Entity | Description |
|------|---|---|
| `/apanel` | — | Dashboard | Stats widgets & system audit logs |
| `/apanel/locales` | `locales` | Locale | Manage active locales and text directions |
| `/apanel/translations` | `translation-keys` | TranslationKey | Dictionary keys & translations editing |
| `/apanel/settings` | `settings` | Setting | System contacts, phone, and SEO values |
| `/apanel/menus` | `menus` | Menu | Header/Footer active menu layout nodes |
| `/apanel/menu-items` | `menu-items` | MenuItem | Localized navigation link anchors |
| `/apanel/pages` | `pages` | Page | Site static structure pages |
| `/apanel/page-blocks` | `page-blocks` | PageBlock | Homepage Rector section, Hero stats, and banners |
| `/apanel/faculties` | `faculties` | Faculty | Academic faculties |
| `/apanel/departments` | `departments` | Department | Sub-faculty departments |
| `/apanel/programs` | `programs` | Program | Bachelor/Master study curricula |
| `/apanel/courses` | `courses` | Course | curriculum course modules |
| `/apanel/news` | `news` | News | Localized press items |
| `/apanel/announcements` | `announcements` | Announcement | Campus alert banners |
| `/apanel/staff` | `staff` | StaffProfile | Faculty deans and administrative leaders |
| `/apanel/services` | `services` | Service | Campus dormitories and priority priority |
| `/apanel/videos` | `videos` | Video | Promo and university videos |
| `/apanel/media` | `media` | Media | File assets library (Browse, select, delete) |
| `/apanel/users` | `users` | User | Login profiles |
| `/apanel/roles` | `roles` | Role | System roles mapping |
| `/apanel/permissions` | `permissions` | Permission | System permissions mapping |
| `/apanel/students` | `students` | StudentProfile | Applicant profile details |
| `/apanel/applications` | `applications` | Application | Admissions status, contract generation |
| `/apanel/application-documents` | `application-documents`| ApplicationDocument| Uploaded passports, diplomas, and transcripts |
| `/apanel/contracts` | `contracts` | Contract | Billing tuition fees invoices |
| `/apanel/payments` | `payments` | Payment | Billing payment slips |
| `/apanel/inquiries` | `inquiries` | Inquiry | Support contact queries |
| `/apanel/support-tickets` | `support-tickets` | SupportTicket | admissions support tickets |
| `/apanel/comments` | `comments` | Comment | Article comments feed |
| `/apanel/notifications` | `notifications` | Notification | Student system alert notifications |
| `/apanel/audit-logs` | `audit-logs` | AuditLog | Admin actions logs (Read Only) |
| `/apanel/green-campus-stats` | `green-campus-stats` | GreenCampusStat | Sustainability metric numbers |
| `/apanel/green-campus-articles`| `green-campus-articles`| GreenCampusArticle| Campus greenmetric achievements |
| `/apanel/application-status-histories` | `application-status-histories` | ApplicationStatusHistory | Admissions status history records |

---

## 4. Key Workflows

### 4.1 Translation Tab Management

For translated entities, `FormBuilder.jsx` renders translation tabs (`English`, `Uzbek`, `Russian`, `Arabic`). It automatically warns the administrator by marking tabs containing empty fields with an **Alert warning icon** to prevent incomplete content updates.

The resulting JSON request payload maps translations into standard Eloquent layouts:

```json
{
  "slug": "chemistry-dept",
  "is_active": true,
  "translations": {
    "en": { "name": "Department of Chemistry", "description": "Overview..." },
    "uz": { "name": "Kimyo kafedrasi", "description": "Tavsif..." },
    "ru": { "name": "Кафедра химии", "description": "Описание..." },
    "ar": { "name": "قسم الكيمياء", "description": "ملخص..." }
  }
}
```

### 4.2 Application Processing

The admissions screen `/apanel/applications/:id` allows:

1. **Document Verification:** Approving, rejecting, or requesting replacement uploaded files through the `application-documents` resource.
2. **Status Transitions:** Advancing applications through standard pipelines: `draft` $\rightarrow$ `submitted` $\rightarrow$ `under_review` $\rightarrow$ `missing_documents` $\rightarrow$ `accepted` $\rightarrow$ `rejected` $\rightarrow$ `contract_pending` $\rightarrow$ `payment_pending` $\rightarrow$ `enrolled` $\rightarrow$ `active_student` $\rightarrow$ `graduated`.
3. **Audit Logs:** Saving status comment notes into `application_status_histories`.
4. **Student Notification:** Sending notification rows to the applicant user's notification feed.

### 4.3 Media Library Pickers

The `/apanel/media` view lists uploaded files from the media resource. Administrators can:

* Upload new files using file select dialogue.
* Click **Copy Path** to quickly write path links (e.g. `/storage/media/filename.jpg`) to the clipboard.
* Choose files inline within CRUD forms using the **Browse** picker.

Uploaded application documents are downloaded through the specialized protected route `GET /api/v1/apanel/application-documents/{id}/download`.

---

## 5. Security & Gatekeepers

1. **Routes Protection:** Wrapped by `<AdminRoute>` inside `App.jsx` to prevent rendering content before validation completes.
2. **Redirection:** Unauthenticated users are redirected to `/apanel/login`.
3. **Role Guard:** Validates if the authenticated user has the `'apanel'` slug in their roles array fetched via `/api/v1/auth/user`. If missing, renders a **403 Forbidden** error screen.
4. **Backend Middleware:** Every `/api/v1/apanel/*` endpoint requires `auth:sanctum`, `role:apanel`, and the `apanel-api` rate limiter.
5. **Backend Whitelist:** The dynamic CRUD controller can only access resources explicitly listed in `AdminCrudController::$whitelist`.
6. **Audit Trail:** Create, update, and delete actions write `audit_logs` unless the edited resource is the audit log itself.
7. **Cache Invalidation:** Writes to public CMS resources rotate the `public_content_cache_version` key so public cached responses refresh on the next request.

### Media Upload Limits

Apanel media upload accepts PDF, JPEG, PNG, WebP, MP4, AVI, and MOV files up to 50 MB. Executable uploads are not allowed by validation. Files are stored on Laravel's public disk and returned through `/storage/...` URLs for public media records.

---

## 6. How to Add a New apanel Resource

To register and manage a new database model under apanel:

1. **Backend Whitelist:** Open `AdminCrudController.php` and append the table slug and model class to `$whitelist`:

    ```php
    'my-resource' => \App\Models\MyModel::class,
    ```

2. **Backend Rules:** Add validation cases inside `getValidationRules()`:

    ```php
    case 'my-resource':
        return [
            'code' => 'required|string',
            'is_active' => 'boolean'
        ];
    ```

3. **Frontend Schema:** Open `ApanelCrud.jsx` and add the schema layout config mapping columns, sort settings, and form input controls under `RESOURCE_SCHEMAS`:

    ```javascript
    "my-resource": {
      title: "My Resources",
      columns: [
        { key: "code", label: "Code", sortable: true },
        { key: "is_active", label: "Active Status", type: "boolean" }
      ],
      fields: [
        { name: "code", label: "Code", type: "text", required: true },
        { name: "is_active", label: "Active Status", type: "boolean" }
      ]
    }
    ```

4. **Sidebar Link:** Open `ApanelLayout.jsx` and append the path `/apanel/my-resource` to the `menuCategories` list.

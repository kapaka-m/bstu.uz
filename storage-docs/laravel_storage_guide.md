# BSTU International — File Storage Configuration Guide

This directory documents the file storage architecture and public/private asset path resolutions for BSTU International.

---

## Storage Disks

Laravel uses two local disks:

- `public`: public CMS media exposed through `/storage/...`.
- `local`: private student workflow files under `apps/api/storage/app/private`.

Public CMS files are organized under:

- `storage/app/public/cms/about-page/`
- `storage/app/public/cms/administration/`
- `storage/app/public/cms/branding/`
- `storage/app/public/cms/blog/`
- `storage/app/public/cms/green-campus/`
- `storage/app/public/cms/home/hero/`
- `storage/app/public/cms/media-library/YYYY/MM/DD/`
- `storage/app/public/cms/news-events/`
- `storage/app/public/cms/staff/`
- `storage/app/public/cms/university-centers/`
- `storage/app/public/cms/uploads/images/YYYY/MM/DD/`
- `storage/app/public/cms/videos/files/`
- `storage/app/public/cms/videos/thumbnails/`

Branding assets such as favicons, app icons, and locale-specific logos are stored in `cms/branding/`. Their active paths are controlled by public `settings` records with keys like `branding_logo_en` and `branding_favicon_png`.

Home hero images are stored in `cms/home/hero/`. Their active paths are controlled by public `settings` records:

- `home_hero_background_image`
- `home_hero_main_image`

Private workflow files are organized under:

- `storage/app/private/applications/{application_id}/documents/`
- `storage/app/private/applications/{application_id}/receipts/application-fees/`
- `storage/app/private/applications/{application_id}/receipts/contract-payments/`
- `storage/app/private/applications/{application_id}/receipts/service-fees/`
- `storage/app/private/generated/admissions/`
- `storage/app/private/generated/contracts/`
- `storage/app/private/generated/enrollments/`
- `storage/app/private/generated/prikazes/`

---

## Public Symlink

To make uploads accessible to the React web app and Flutter mobile app, a symbolic link must be created from Laravel's public directory:

```bash
cd apps/api
php artisan storage:link
```

This links `apps/api/public/storage` directly to `apps/api/storage/app/public`.

On this Windows workspace, prefer the local PHP wrapper:

```bash
cd apps/api
.\php-local.bat artisan storage:link
```

If `public/storage` reports `NOT LINKED` in `artisan about`, recreate the link with the command above. Do not delete uploaded files from `storage/app/public` during cleanup.

On Windows, `public/storage` may appear as a junction/reparse point. This is expected. Files listed under both `public/storage/...` and `storage/app/public/...` are normally the same physical files viewed through the public link, not duplicates.

---

## Path Resolution

Do not repeat storage URL construction in pages, components, or service files.
Use the shared storage/media helpers in each client:

- Web: `apps/web/src/lib/storage.js`.
- Mobile: the central app configuration/storage helper under `apps/mobile`.

The helper must resolve API-returned file paths against the environment-specific
Laravel host and must not append duplicate `/api/v1` segments. Local development
URLs may be used in `.env` examples only; production deployments should rely on
environment variables and the shared helper layer.

## Upload Security

- Student application documents are private workflow files stored on the `local` disk and should be downloaded only through authenticated student/apanel routes.
- Public media records may expose files through `/storage/...` URLs only when `is_public` is true.
- Allowed student document types: PDF, JPEG, PNG, WebP; max 10 MB.
- Allowed apanel media types: PDF, JPEG, PNG, WebP, MP4, AVI, MOV; max 200 MB.
- Store generated logs, framework cache, and test result cache out of source control. Uploaded media must remain in storage and should be backed up before deployment changes.
- Review canonical files from `storage/app/public`, not through `public/storage`, when checking whether an upload should be quarantined.
- Use `php-local.bat artisan bstu:organize-storage` after manual legacy imports if old storage prefixes need to be normalized into the current structure.

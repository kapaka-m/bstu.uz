# BSTU International — File Storage Configuration Guide

This directory documents the file storage architecture and public/private asset path resolutions for BSTU International.

---

## Storage Disks

Laravel is configured to utilize the `public` disk for user-uploaded documents and media assets:

- **Local Location**: `apps/api/storage/app/public/`
- **Subdirectories**:
  - `media/`: Public photos, faculty images, and program banners.
  - `documents/`: Student passport copies, transcripts, and diploma scans.

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

On Windows, `public/storage` may appear as a junction/reparse point. This is expected. Files listed under both `public/storage/documents` and `storage/app/public/documents` are normally the same physical files viewed through the public link, not duplicates.

---

## Path Resolution

### Web App (`apps/web`)

When rendering images or referencing uploaded files, prefix the `file_path` returned by the API:

```javascript
const absoluteUrl = filePath.startsWith("http") 
  ? filePath 
  : `/storage/${filePath}`;
```

### Mobile App (`apps/mobile`)

When fetching files on mobile, resolve path names against the dynamic base host:

```dart
final absoluteUrl = filePath.startsWith("http")
  ? filePath
  : '${AppConfig.defaultApiBaseUrl}/storage/${filePath}';
```

**Important:** remove the `/api/v1` suffix from the API base URL when resolving storage paths. For example, if the API base is `http://127.0.0.1:8000/api/v1`, file URLs should resolve against `http://127.0.0.1:8000/storage/...`.

## Upload Security

- Student application documents are private workflow files and should be downloaded only through authenticated student/apanel routes.
- Public media records may expose files through `/storage/...` URLs only when `is_public` is true.
- Allowed student document types: PDF, JPEG, PNG, WebP; max 10 MB.
- Allowed apanel media types: PDF, JPEG, PNG, WebP, MP4, AVI, MOV; max 50 MB.
- Store generated logs, framework cache, and test result cache out of source control. Uploaded media must remain in storage and should be backed up before deployment changes.
- Review canonical files from `storage/app/public`, not through `public/storage`, when checking whether an upload should be quarantined.

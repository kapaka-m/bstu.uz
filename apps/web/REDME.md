# BSTU Web Frontend Audit

This file documents the current `apps/web` frontend structure and the rules used to keep production content out of the React source.

## Purpose

`apps/web` is the React 19, Vite, and Tailwind CSS 4 frontend for the BSTU International website, student portal, and admin panel. It must not contain hard-coded production content for the public site. Public content is loaded from the Laravel API and the MySQL-backed CMS.

## Verified Content Sources

Public website data is loaded through API services:

- Home CMS sections: `src/services/homeCmsService.js`
- Locales, translations, and public settings: `src/services/translationService.js`
- Header menu: `src/services/menuService.js`
- Footer content: `src/services/footerService.js`
- Contact page: `src/services/contactService.js`
- Faculties: `src/services/facultyService.js`
- Departments: `src/services/departmentService.js`
- Programs and courses: `src/services/programService.js`
- Services: `src/services/serviceService.js`
- News and events: `src/services/newsService.js`
- Blog: `src/services/blogService.js`
- Announcements: `src/services/announcementService.js`
- Videos: `src/services/videoService.js`
- Green campus content: `src/services/greenCampusService.js`
- Administration profiles: `src/services/administrationService.js`
- University centers: `src/services/centerService.js`

Global public data is assembled in `src/context/AppDataContext.jsx`. Locale, translation, site settings, branding, document language, document direction, and header menu are managed in `src/context/LocaleContext.jsx`.

## Static Content Policy

Allowed static source:

- Route definitions.
- Form field schemas.
- Admin panel resource schemas.
- Empty CMS form templates.
- Icon maps.
- Color maps.
- Status keys and workflow keys.
- Translation keys passed to `t()`.
- Technical defaults such as empty strings, empty arrays, and route paths.

Not allowed static source:

- Public page text used as production copy.
- Faculty, department, program, staff, news, blog, announcement, video, contact, footer, or home content.
- Runtime fallback data copied from old React or Dart data files.
- Public debug labels, audit labels, raw source labels, or temporary task notes.

## Current Audit Result

No `src/data` runtime source was found.

No imports from `src/data` were found.

No production public content arrays were found in `src/pages`, `src/sections`, `src/components`, or `src/services`.

CMS editor defaults in admin pages are empty templates only. They exist so editors can create or update records safely; they are not used as public production content.

## Validation

The following commands were run from `apps/web`:

```bash
npm.cmd run lint
npm.cmd run build
```

Both commands completed successfully with no ESLint errors. The Vite/Rolldown plugin timing diagnostic is disabled in `vite.config.js` with `build.rolldownOptions.checks.pluginTimings = false` so production builds stay clean.

## Maintenance Rules

Do not add a `src/data` folder for runtime content.

Do not import static content into public pages.

Do not add hard-coded public copy as fallback text. Missing CMS text should render empty or use translation keys from the API-backed translation system.

Do not store production images or media paths as fixed frontend data. Use API-provided media paths with `publicAssetUrl`.

Keep durable frontend guidance in this file, root `AGENTS.md`, or source comments only when the code needs clarification.

# Localization Review - 2026-09-19

## Scope and Status

The reported department head/staff language defect is repaired and verified. Broader localization review covered database translation tables, public API responses, static translation references, and selected shared frontend paths. This is not a completed whole-project production-readiness audit.

## Root Causes and Repairs

- Staff translation rows contained copied English positions, generated biographies, and office descriptions despite their non-English locale. Targeted, idempotent repairs now persist localized content in MySQL and durable backend seed data.
- The same inventory found copied course descriptions and interface values. The initial repair changed 2,322 existing rows / 2,751 fields across nine tables; subsequent interface seed operations added four-language UI keys.
- Shared admin table and content selectors now prefer the selected language rather than English or the first translation row.
- LocaleContext ignores stale asynchronous language responses.
- Direct visible literals across admin/student/public components now use the existing database-backed translation service. Missing or object-valued translation key references were corrected.
- Staff saves require all four languages and reject known English-copy patterns. An ESLint rule and a read-only database command provide repeatable regression checks.
- The existing faculty dropdown on `/programs` is retained; its label now uses the database translation directly.

## Verified Evidence

- Local MySQL data was backed up before mutation. No database reset, truncation, or full database seed was run. Translation backup retained in ignored local audit storage.
- Database audit: 39 translation tables, 3,540 logical records, zero findings under the implemented completeness/pattern checks.
- Static literal translation references: 1,736 references resolved to nonempty strings in all four locales after final cleanup.
- Public API sweep: 34 endpoints x four languages = 136 successful responses. Its 54 candidate flags were category/tag enum values (`Events`), not display labels; consumers use separate localized labels.
- Department browser regression: 20 language/viewport cases, 15 staff cards, correct head titles for en/uz/ru/ar, correct document direction, no horizontal overflow, no captured console errors or failed requests. Widths: 375, 768, 1366, 1920 pixels.
- Faculty dropdown browser regression: four languages, selection/reset, populated labels after translation loading, no page errors, and no horizontal overflow at 375 and 1366 pixels.
- Backend tests: 9 passed, 60 assertions, isolated in-memory SQLite.
- Frontend tests: 3 passed. ESLint, production build, and scoped PHP Pint checks passed.

## Remaining Work and Limits

- The configuration-source inventory still identified 619 candidate occurrences / 485 distinct strings, chiefly admin form schemas, configuration captions, and related metadata. These require consumer-by-consumer triage and translation; not every candidate is necessarily visible UI. Direct JSX lint does not cover these sources.
- Notification/error strings, computed translation keys, and all authenticated admin/student workflows have not received exhaustive browser localization verification.
- The database audit recognizes reviewed English strings and generated templates; zero findings does not prove every sentence is linguistically correct. Editorial review remains necessary, particularly for specialist academic titles and prose.
- Existing cross-language fallback remains for compatibility if CMS data later becomes incomplete. Run the audit after imports and before releases.
- Staff-specific validation is not a universal content-language classifier or publication gate for every CMS resource.

## Repeatable Checks

See README section "Content Localization Checks". The runtime source of truth remains Admin/CMS -> Laravel -> MySQL -> public API -> React. No legacy frontend data fallback or parallel translation system was introduced.

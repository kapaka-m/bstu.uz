---

name: international-bstu-uz
description: >
  End-to-end audit, repair, testing, and production-readiness verification
  for BSTU International application pages and route families, covering
  frontend, backend, APIs, database, Admin/CMS, CRUD/synchronization,
  authentication/authorization, i18n/RTL, media, SEO, security,
  accessibility, performance, browser runtime, responsive behavior,
  production configuration, regression safety, and deployment readiness.
version: "2.0"
execution_mode: autonomous
scope: target-route-and-direct-dependencies
---

---

# CODEX SKILL — END-TO-END PAGE AUDIT, REPAIR & PRODUCTION READINESS

# 0. PURPOSE

This skill defines the authoritative procedure for auditing, repairing, testing, and verifying pages and route families inside the **BSTU International Website** repository.

It is designed for requests such as:

```text
Audit:
http://localhost:5173/about
```

or:

```text
Audit:
http://localhost:5173/announcements/*
```

or:

```text
Audit:
http://localhost:5173/announcements
http://localhost:5173/announcements/*
```

The objective is not merely to inspect source code.

The objective is to determine, using the strongest safe evidence available, whether the supplied target is genuinely:

- functional
- correctly connected
- database-consistent
- administratively manageable
- secure
- properly validated
- localized correctly
- responsive
- accessible
- performant
- maintainable
- production-safe
- deployment-ready

When invoked, begin work immediately.

Do not ask the user to restate instructions already contained in this skill.

Do not ask for ordinary confirmation before safe inspection, diagnosis, testing, or target-related repairs.

---

# 1. REPOSITORY CONTEXT

This skill operates inside the **BSTU International Website monorepo**.

Expected repository root:

```text
C:\Users\KAPAKA\Desktop\international.bstu.uz
```

Expected repository structure:

```text
international.bstu.uz/
│
├── apps/
│   ├── web/
│   └── api/
│
├── AGENTS.md
├── README.md
├── SKILL.md
├── international.bstu.uz.code-workspace
└── .gitignore
```

Before executing this skill:

1. Read and obey the repository-root `AGENTS.md`.
2. Use `README.md` as durable project/developer context where needed.
3. Treat this `SKILL.md` as the authoritative execution protocol for page and route-family audits.

Repository-wide architecture, database safety, Git safety, security, and coding conventions in `AGENTS.md` remain in force during this audit.

Do not duplicate, bypass, or silently override repository-wide rules.

---

# 2. DOCUMENT RESPONSIBILITIES

The repository documentation has separate responsibilities:

## `README.md`

Use for:

- project overview
- developer setup
- architecture summary
- development workflow
- deployment basics

## `AGENTS.md`

Use for:

- repository-wide coding-agent behavior
- architecture constraints
- project conventions
- database safety
- security rules
- Git/change rules

## `SKILL.md`

Use for:

- page audits
- route audits
- route-family audits
- end-to-end runtime verification
- repair workflow
- production-readiness decisions

Do not move the complete contents of this skill into `AGENTS.md`.

Do not duplicate this skill under multiple filenames unless explicitly requested.

`SKILL.md` is the page-audit source of truth.

---

# 3. ROLE

Act simultaneously as the project's:

- Senior Full-Stack Engineer
- Software Architect
- QA Engineer
- E2E Engineer
- Database Engineer
- Security Reviewer
- Accessibility Reviewer
- Performance Engineer
- DevOps / Production Readiness Auditor

Your job is not to produce a reassuring report.

Your job is to maximize **real confidence backed by evidence**.

---

# 4. INVOCATION

This skill may be invoked explicitly or through `AGENTS.md`.

Valid requests include:

```text
Audit:
http://localhost:5173/about
```

```text
Audit:
http://localhost:5173/announcements/*
```

```text
Audit according to SKILL.md:
http://localhost:5173/about
```

```text
Audit these as one feature:
http://localhost:5173/announcements
http://localhost:5173/announcements/*
```

The user does not need to provide the absolute path to this file when the agent is already operating inside the repository.

If an explicit path is provided, for example:

```text
C:\Users\KAPAKA\Desktop\international.bstu.uz\SKILL.md
```

read it and continue normally.

Begin immediately after resolving the supplied target.

---

# 5. INPUT CONTRACT

Accept:

- one exact target URL
- one dynamic route
- one wildcard route family
- multiple related targets

Examples:

```text
http://localhost:5173/
http://localhost:5173/about
http://localhost:5173/announcements
http://localhost:5173/announcements/*
http://localhost:5173/programs/*
```

Do not require special syntax if the user's intent is clear.

---

# 6. PROJECT ROOT DISCOVERY

Do not blindly assume the working directory.

Confirm the repository root using evidence such as:

- `.git`
- `AGENTS.md`
- `README.md`
- `SKILL.md`
- `apps/web`
- `apps/api`
- `package.json`
- `composer.json`
- workspace configuration

When this skill is located at the repository root, prefer that directory and its descendants.

Do not guess architecture solely from folder names.

---

# 7. TARGET SEMANTICS

## 7.1 Exact Page

Example:

```text
http://localhost:5173/about
```

Audit that exact page and all direct dependencies required for it.

---

## 7.2 Wildcard Route Family

Example:

```text
http://localhost:5173/announcements/*
```

Interpret `/*` as:

> Audit the real route family beneath this path.

Do not treat the wildcard itself as a literal browser URL.

Inspect the actual frontend router and discover the implemented child/dynamic routes.

Possible patterns might include:

```text
/announcements/:id
/announcements/:slug
/announcements/category/:slug
```

These are examples only.

Never invent routes that are not actually implemented.

---

## 7.3 Base Route + Wildcard

If the user supplies:

```text
http://localhost:5173/announcements
http://localhost:5173/announcements/*
```

audit them as one connected feature family.

Include where relevant:

- list/index page
- detail routes
- filters
- search
- sorting
- pagination
- navigation list → detail
- detail → list/back navigation
- API contracts
- shared components
- database relationships
- Admin/CMS management
- empty states
- invalid/not-found detail routes

Do not duplicate the same investigation unnecessarily.

---

# 8. REPRESENTATIVE ROUTE-FAMILY TESTING

For dynamic route families, discover actual routable records from the API/database.

When sufficient data exists, test representative cases such as:

1. A normal valid published record.
2. A record with optional/null fields where available.
3. A record containing media where applicable.
4. A record with long text where useful for responsive verification.
5. Relevant locales where content behavior differs.
6. An invalid/nonexistent slug or identifier.
7. An unpublished/inactive record where access behavior matters.

Do not create fake URLs when valid route examples can be discovered from project data.

For large route families, representative runtime testing is acceptable when:

- the shared implementation is confirmed
- API/schema behavior is shared
- representative edge cases are covered

Do not claim every database record was individually browser-tested unless that actually occurred.

---

# 9. EXECUTION PRINCIPLE

Follow this lifecycle:

```text
DISCOVER
    ↓
TRACE
    ↓
EXECUTE
    ↓
VERIFY
    ↓
DIAGNOSE
    ↓
FIX
    ↓
RETEST
    ↓
REGRESSION CHECK
    ↓
REPORT
```

For every relevant area:

1. Inspect the real implementation.
2. Trace the real dependency path.
3. Execute the strongest safe verification available.
4. Verify actual runtime/data behavior.
5. Identify root causes.
6. Apply the smallest safe fix.
7. Retest.
8. Check regression impact.
9. Report only what evidence supports.

Never guess.

---

# 10. EVIDENCE PRIORITY

Prefer stronger evidence over weaker evidence.

Use this order:

1. Real end-to-end browser + API + database verification
2. Runtime integration verification
3. API/database execution
4. Automated E2E/integration tests
5. Feature/unit tests
6. Build/type/lint/static checks
7. Code-path inspection
8. Assumption

An assumption is never acceptable as `PASS`.

---

# 11. VERIFICATION STATES

Use only:

## PASS

Actually executed and successfully verified with appropriate evidence.

## CODE-VERIFIED

Implementation was fully traced and appears correct, but equivalent runtime verification could not safely/practically be completed.

## FAIL

Confirmed broken or incorrect.

## NOT VERIFIED

Insufficient evidence.

## N/A

Genuinely not applicable.

Never convert `NOT VERIFIED` into `PASS`.

---

# 12. VERIFICATION ESCALATION RULE

`CODE-VERIFIED` and `NOT VERIFIED` are fallback states, not shortcuts.

Before using either, ask internally:

> Is another safe verification method available?

Examples:

- Playwright unavailable → check Chrome/Cypress/Puppeteer/Selenium.
- Browser framework absent → inspect local Chrome/Edge.
- API returns 200 → inspect payload/schema.
- Migration exists → inspect actual schema.
- CSS looks responsive → test real viewports.
- CRUD code exists → verify mutation path.
- Live mutation unsafe → check existing integration/E2E tests.

Do not stop at the first unavailable tool.

---

# 13. VERIFICATION LEDGER / CONTINUATION RULE

During an active audit, maintain an internal verification ledger.

Track production-critical areas using:

- PASS
- CODE-VERIFIED
- FAIL
- NOT VERIFIED
- N/A

Also track the evidence establishing each state.

If the user asks to continue the same audit:

- do not restart from zero
- do not repeat unaffected verified work
- invalidate only checks affected by changes
- rerun downstream checks that may regress
- rerun final build/browser/regression verification where appropriate

Example:

If only a responsive React component changes, rerun:

- relevant lint/type checks
- production build
- browser runtime
- responsive viewports
- affected interactions
- affected locales
- shared-component regression checks

Do not unnecessarily rerun unrelated database discovery when the data layer did not change.

---

# 14. AUDIT SCOPE

Audit:

```text
Target URL
→ Router
→ Layout
→ Page
→ Components
→ Hooks/Stores/Context
→ API Client
→ API Endpoint
→ Middleware
→ Validation
→ Controller
→ Service/Repository
→ Model
→ Database
→ Admin/CMS
→ CRUD/Synchronization
→ Authentication/Authorization
→ i18n
→ Media/Storage
→ Accessibility
→ SEO
→ Performance
→ Production Configuration
→ Build
→ Browser Runtime
→ Responsive Runtime
→ End-to-End Flow
```

Do not stop at the visible frontend.

Do not turn the audit into an unrelated repository rewrite.

---

# 15. PHASE A — PROJECT DISCOVERY

Identify:

- frontend framework/version
- backend framework/version
- routing system
- database
- ORM/query layer
- authentication system
- authorization/roles/policies
- Admin/CMS architecture
- API architecture
- state management
- validation libraries
- i18n system
- storage/media architecture
- build tooling
- test tooling
- browser/E2E tooling
- environment mechanism
- package manager
- monorepo/workspace structure

Use existing project conventions.

Do not replace architecture merely because another design is possible.

---

# 16. PHASE B — RESOLVE TARGET IMPLEMENTATION

Locate:

- frontend route
- route parameters
- parent route
- page component
- layout
- child components
- shared components
- hooks
- stores
- contexts/providers
- API clients
- services
- utilities
- types/interfaces
- translations
- assets
- SEO configuration
- environment variables
- route guards

Do not infer filenames from URLs.

Search and confirm.

---

# 17. PHASE C — DEPENDENCY MAP

For every important dynamic section, determine:

```text
UI Section
→ Component
→ Hook/State/Context
→ API Client
→ Endpoint
→ Middleware
→ Validator
→ Controller
→ Service/Repository
→ Model
→ Database Table(s)
→ Translation Source
→ Media Source
→ Admin/CMS
```

Document meaningful branches.

Every public dynamic field should have an intended source.

---

# 18. PHASE D — FRONTEND AUDIT

## Rendering

Check:

- rendering logic
- conditional rendering
- nullable values
- state synchronization
- props
- hooks/effects
- dependency arrays
- cleanup
- memoization where useful
- error boundaries where applicable
- hydration where applicable
- safe image/media rendering

## Data Handling

Check:

- requests
- parameters
- response parsing
- loading states
- empty states
- error states
- retries
- race conditions
- stale state
- cancellation
- unhandled promises
- malformed payload handling

## User Interaction

Actually test relevant:

- buttons
- links
- forms
- CTAs
- menus
- dropdowns
- tabs
- modals
- search
- filters
- sorting
- pagination
- uploads
- language switching
- interactive cards
- external links
- list/detail navigation

No unexplained dead control is acceptable.

## Code Problems

Search for:

- dead code
- duplicate code
- invalid imports
- unused imports
- missing dependencies
- hardcoded dynamic content
- hardcoded URLs
- mock/demo data
- temporary workarounds
- TODO/FIXME
- console/debug artifacts
- runtime errors
- unsafe fallbacks

Fix confirmed issues safely.

---

# 19. PHASE E — RESPONSIVE RUNTIME

Minimum test viewports:

```text
Mobile:  375 × 812
Tablet:  768 × 1024
Laptop:  1366 × 768
Desktop: 1920 × 1080
```

Use project-defined breakpoints if clearly more appropriate.

Inspect the **full rendered document**, not only the initial viewport.

Check:

- Header
- Navigation
- Page title/Hero
- Main sections
- Cards
- Lists
- Tables
- Forms
- Images
- Videos
- Modals
- Pagination
- Footer
- Sticky/fixed elements
- Late-loaded content

Also check:

```text
document.scrollWidth <= expected viewport width
```

unless horizontal scrolling is intentionally required.

Look for:

- horizontal overflow
- clipped text
- overlap
- off-screen controls
- broken grids
- malformed cards
- image distortion
- broken navigation
- excessive whitespace
- unreadable content
- RTL breakage

Do not mark responsiveness PASS from Tailwind/CSS inspection alone.

---

# 20. PHASE F — ACCESSIBILITY

Check where relevant:

- semantic HTML
- heading hierarchy
- labels
- input associations
- alt text
- keyboard navigation
- focus visibility
- focus order
- button/link semantics
- appropriate ARIA
- non-color-only state communication

Use runtime verification where possible.

Do not claim full WCAG compliance unless tested to that standard.

---

# 21. PHASE G — API / DATA CONTRACT

Compare:

```text
Frontend expectation
↔
Actual backend response
```

Verify:

- names
- types
- nullability
- nested objects
- arrays
- IDs
- pagination
- translations
- media URLs
- dates
- booleans
- statuses
- optional values
- error payloads

If TypeScript or equivalent types exist, compare them with actual backend output.

---

# 22. HTTP 200 IS NOT API VERIFICATION

For each major endpoint inspect:

- request URL
- method
- HTTP status
- content type
- schema
- required fields
- nested structure
- relevant types
- locale behavior
- pagination
- media URLs
- absence of hidden errors
- frontend compatibility

Forbidden logic:

```text
HTTP 200
therefore API works
```

A malformed `200` response is not a pass.

---

# 23. PHASE H — BACKEND AUDIT

Inspect:

- routes
- middleware
- controllers
- validators
- services
- repositories
- models
- resources/serializers
- policies
- authentication
- authorization
- exception handling

Check:

- methods
- status codes
- request shape
- response shape
- validation
- sanitization
- pagination
- filtering
- sorting
- errors
- N+1
- repeated queries
- eager loading
- unbounded queries
- unsafe raw queries
- mass assignment
- exposed sensitive fields

Fix confirmed issues while preserving contracts when practical.

---

# 24. PHASE I — DATABASE

Compare:

```text
Migration
↔ Actual DB Schema
↔ Model
↔ Service
↔ Controller
↔ API
↔ Frontend
```

Check:

- tables
- columns
- types
- PKs
- FKs
- indexes
- unique constraints
- nullability
- defaults
- timestamps
- soft deletes
- relationships
- cascades
- referential integrity
- translation tables
- pivot tables

Look for:

- missing fields
- obsolete fields
- incorrect types
- incorrect FKs
- missing indexes
- dangerous cascades
- schema/code drift
- translation inconsistencies

Do not create migrations without a confirmed requirement.

---

# 25. DATABASE ENVIRONMENT CLASSIFICATION

Before mutations determine:

- development
- automated test
- staging
- production
- unknown

Inspect config without revealing credentials.

Never assume:

```text
localhost = disposable database
```

Unknown means potentially important.

---

# 26. DATABASE SAFETY

Never automatically:

- drop tables
- reset databases
- truncate data
- delete production records
- run destructive seeders
- remove important columns
- rewrite migration history
- destroy relationships

Prefer:

- additive migrations
- reversible migrations
- backward-compatible changes
- safe indexes
- safe constraints

If destructive work is necessary, report it instead of executing it automatically.

---

# 27. PHASE J — ADMIN / CMS

Mandatory whenever content is dynamic or managed.

Discover relevant:

- page CMS
- services
- programs
- faculties
- announcements
- news
- blog
- leadership
- statistics
- contacts
- media
- documents
- translations
- SEO
- visibility
- ordering

Verify:

```text
Admin UI
→ Request
→ Authentication
→ Authorization
→ Validation
→ Controller
→ Service
→ Model
→ Database
→ Public API
→ Frontend
→ Browser
```

Look for:

- disconnected fields
- unmanaged DB fields
- hardcoded public data
- saves that never reach public output
- frontend fields with no source

---

# 28. ADMIN MUTATION ARCHITECTURE

Do not infer CRUD from method names.

Identify whether:

- POST creates
- PUT/PATCH updates
- DELETE removes
- or aggregate synchronization handles multiple operations

If aggregate synchronization exists, inspect whether it:

- creates
- updates
- removes
- soft-deletes
- restores
- reorders
- changes status
- syncs translations
- syncs relationships
- replaces media
- removes old media

`PUT exists` does not mean `full CRUD verified`.

---

# 29. CRUD / SYNCHRONIZATION

Verify where applicable:

## CREATE

- form/request
- validation
- persistence
- relationships
- translations
- media
- public API exposure

## READ

- Admin values
- public API
- public frontend

## UPDATE

- existing values
- persistence
- relationships
- translations
- media
- public API
- frontend

## DELETE / REMOVE

- intended record
- relationships
- files
- API
- frontend missing-state handling

Also verify:

- active/inactive
- publish/unpublish
- reorder
- search
- filters
- pagination
- preview
- restoration

---

# 30. SAFE REVERSIBLE CMS MUTATION

When environment safety is established, prefer a real reversible mutation.

Procedure:

1. Select a safe existing development/test/staging textual value.
2. Record the exact original value.
3. Create a temporary unique value:

```text
AUDIT_TEST_<timestamp>
```

4. Change it using the real authenticated Admin/API flow.
5. Verify mutation succeeds.
6. Verify DB.
7. Verify public API.
8. Verify frontend.
9. Verify browser.
10. Restore exact original value through the real app path.
11. Verify DB restoration.
12. Verify API restoration.
13. Verify browser restoration.
14. Confirm temporary value is gone.

Never perform against confirmed production data.

---

# 31. MUTATION ROLLBACK GUARANTEE

Treat reversible mutation as a transactional audit operation.

Before mutating:

1. Capture original state.
2. Preserve rollback data.
3. Prepare restoration before continuing.

If any later step fails:

1. Stop normal verification.
2. Restore original state immediately.
3. Verify restoration.
4. Only then continue diagnosis.

Never leave temporary audit data because a later test failed.

---

# 32. EQUIVALENT CMS EVIDENCE

A live reversible mutation is preferred high-confidence evidence, but it is not the only acceptable evidence.

Equivalent strong evidence may include:

- E2E tests against isolated DB
- integration tests
- transactional Laravel feature tests
- isolated staging workflows
- disposable automated test databases

Do not downgrade an otherwise strongly verified page solely because real development data was intentionally not mutated if equivalent strong automated evidence exists.

---

# 33. PHASE K — AUTHENTICATION & AUTHORIZATION

Authentication and authorization are separate.

Test where practical:

## Unauthenticated

Expected:

```text
401
```

or intentional equivalent.

## Authenticated Non-Admin

Expected:

```text
403
```

or intentional equivalent.

## Authorized Admin

Expected successful access.

Do not treat `401` as proof that role authorization works.

---

# 34. PHASE L — SECURITY

Review:

- XSS
- CSRF
- SQL injection
- mass assignment
- unsafe HTML
- unsafe redirects
- path traversal
- upload abuse
- MIME spoofing
- upload size
- sensitive data
- debug output
- stack traces
- credentials
- tokens
- API keys
- secrets
- internal paths

Never print secrets.

Security verification must remain non-destructive.

Do not perform:

- brute-force credential testing
- denial-of-service testing
- destructive penetration tests
- uncontrolled fuzzing
- destructive payloads

Security testing must remain scoped to application correctness, validation, access control, and safe request behavior.

---

# 35. PHASE M — MEDIA / STORAGE

Verify when applicable:

- upload
- validation
- MIME
- extension
- size
- names
- storage
- public URLs
- replacement
- deletion
- orphan cleanup
- fallback
- broken image handling

Search for:

```text
localhost
127.0.0.1
absolute Windows paths
development storage URLs
hardcoded local assets
```

Production must not depend on local-machine paths.

---

# 36. PHASE N — i18n / LOCALIZATION

Discover active locales from project/database.

Verify:

- translation keys
- missing translations
- fallback locale
- DB translations
- API locale behavior
- Admin inputs
- language switch
- localized routes
- locale persistence
- localized SEO
- LTR/RTL

For RTL verify runtime:

- direction
- alignment
- navigation
- cards
- forms
- tables
- modals
- pagination
- directional icons
- mixed-direction content

Switch languages in the browser where possible.

---

# 37. PHASE O — BROWSER TOOLING DISCOVERY

Do not stop because Playwright is absent.

Check for:

- Playwright
- Cypress
- Puppeteer
- Selenium
- Chrome
- Chromium
- Edge
- Firefox
- existing E2E scripts
- existing browser helpers

If no framework exists, consider a temporary/non-invasive browser method.

Do not permanently modify the project for one disposable audit unless justified.

---

# 38. PHASE P — REAL BROWSER RUNTIME

Actually load the target when possible.

Verify:

- page load
- console errors
- console warnings
- uncaught exceptions
- unhandled promises
- framework warnings
- network traffic
- failed XHR/fetch
- relevant 4xx/5xx
- broken assets
- broken images
- missing fonts/resources
- navigation
- buttons
- forms
- modals
- language switching
- target interactions

A screenshot alone is insufficient.

Report:

```text
Console errors:
Relevant console warnings:
Uncaught exceptions:
Unhandled promise rejections:
Failed relevant network requests:
Broken resources/assets:
```

Fix target-related problems and retest.

---

# 39. PAGE HTTP RULE

```text
GET /target => 200
```

does not prove:

- React rendering
- hydration
- JS execution
- console cleanliness
- API integration
- correct content
- responsive design
- interactions
- localization
- accessibility

Never translate page HTTP success into frontend runtime PASS.

---

# 40. PHASE Q — FAILURE STATES

Where safe, verify relevant:

- API unavailable
- API error
- empty dataset
- missing optional fields
- missing image
- broken image URL
- invalid payload
- validation error
- unauthenticated request
- forbidden request
- invalid route parameter
- missing detail record
- missing translation
- slow loading

The page should fail gracefully.

Do not intentionally disrupt production systems.

---

# 41. PHASE R — SEO

Verify:

- title
- description
- canonical
- Open Graph
- Twitter/X
- robots
- structured data
- headings
- image alt

Verify runtime metadata where possible.

No production metadata should contain:

- localhost
- local ports
- filesystem paths
- staging URLs

unless intentionally environment-specific.

---

# 42. PHASE S — PERFORMANCE

## Frontend

Check:

- duplicate requests
- excessive requests
- avoidable sequential calls
- unnecessary rerenders
- large bundles
- oversized media
- blocking resources
- useful lazy loading opportunities
- expensive repeated calculations

## Backend

Check:

- N+1
- duplicate queries
- unnecessary joins
- missing indexes
- unbounded queries
- excessive payloads
- missing pagination
- inefficient translation loading

Avoid speculative micro-optimization.

---

# 43. PHASE T — PRODUCTION CONFIGURATION

Search relevant code/config for:

```text
localhost
127.0.0.1
hardcoded ports
hardcoded domains
hardcoded APIs
hardcoded asset URLs
debug flags
test credentials
temporary credentials
mock
dummy
TODO
FIXME
development fallbacks
```

Verify environment-driven:

- API URL
- frontend URL
- backend URL
- assets
- storage
- CORS
- cookies
- sessions
- Sanctum
- SameSite
- Secure
- HTTPS
- production build

Local values such as:

```text
APP_ENV=local
APP_DEBUG=true
```

are not defects by themselves.

Do not change the developer's local environment to production merely to obtain PASS.

---

# 44. PHASE U — STATIC / BUILD

Use real project commands.

Run applicable:

- type checks
- lint
- frontend build
- backend tests
- frontend tests
- unit tests
- integration tests
- feature tests
- E2E tests
- route listing
- migration status
- schema checks

Classify failures:

```text
TARGET-RELATED
```

or:

```text
PRE-EXISTING / UNRELATED
```

Do not blindly repair unrelated project-wide errors.

---

# 45. PRODUCTION BUILD RULE

Build success alone is not production readiness.

Inspect warnings for:

- unresolved assets
- broken imports
- missing dependencies
- chunk warnings
- environment URL assumptions
- debug/source map concerns
- large target-related assets

Only report Build PASS if the production build actually completed successfully.

---

# 46. ROOT-CAUSE REPAIR POLICY

For every confirmed issue:

1. Reproduce.
2. Identify root cause.
3. Determine smallest safe correction.
4. Implement.
5. Inspect affected dependencies.
6. Retest.
7. Confirm resolution.
8. Check regression impact.

Do not hide errors with:

- empty catch
- disabled validation
- unjustified `any`
- `@ts-ignore`
- `@ts-nocheck`
- disabled lint
- arbitrary hardcoded fallback
- removed authorization
- suppressed exceptions

unless strongly justified by the established architecture.

---

# 47. MODIFIED FILE VERIFICATION

After changing a file:

1. Reopen final file.
2. Check syntax/JSX.
3. Check duplicates.
4. Check imports.
5. Confirm new imports are used.
6. Confirm removed imports are gone.
7. Run relevant checks.
8. Run relevant tests.
9. Run build where appropriate.
10. Inspect diff.

Do not assume an edit is correct merely because the patch command succeeded.

---

# 48. SHARED DEPENDENCY SAFETY

Before modifying shared:

- components
- hooks
- services
- models
- APIs
- tables
- middleware
- layouts
- translations
- utilities

search for other consumers.

Do not fix one page by breaking another.

Run reasonable regression tests.

---

# 49. GIT SAFETY

Before finalizing:

```text
git diff
git status
```

Separate:

## Changes Made During This Audit

from:

## Pre-existing Changes

Do not:

- claim pre-existing changes
- silently revert user changes
- modify unrelated untracked files

Confirm no unintended:

- audit markers
- temporary CMS values
- debug logs
- scripts
- screenshots
- browser helpers
- test artifacts
- unrelated changes

remain in tracked production code.

---

# 50. NO FALSE PASS RULE

The following are NOT equivalent:

```text
HTTP 200
≠ functionality verified

Route exists
≠ feature verified

Controller exists
≠ API verified

API returns 200
≠ API contract verified

Form exists
≠ CRUD verified

PUT endpoint exists
≠ complete CRUD verified

Migration exists
≠ actual schema verified

Model relationship exists
≠ referential integrity verified

Build passes
≠ runtime verified

Responsive CSS exists
≠ responsive runtime verified

Screenshot exists
≠ console/network verified

Code looks correct
≠ production ready
```

---

# 51. PRODUCTION-READY GATE

Do NOT return:

```text
✅ READY FOR PRODUCTION
```

unless all applicable production-critical areas have sufficient evidence.

Normally this includes:

- frontend runtime
- interactions
- backend/API
- API contract
- actual DB schema
- DB relationships
- Admin/CMS
- CRUD/synchronization
- validation
- authentication
- authorization
- i18n/RTL
- media/storage
- accessibility at appropriate scope
- SEO
- performance
- browser console
- browser network
- responsive runtime
- production build
- production config mechanism
- Admin → DB → API → Frontend → Browser where applicable

Equivalent strong automated evidence may replace a live mutation where appropriate.

If important production-critical verification remains unavailable, normally use:

```text
❓ PRODUCTION READINESS NOT FULLY VERIFIED
```

---

# 52. FINAL VERIFICATION

After all fixes:

1. Rerun relevant static checks.
2. Rerun tests.
3. Rerun production build.
4. Recheck API contracts.
5. Recheck DB relationships.
6. Recheck Admin/public sync.
7. Rerun browser.
8. Recheck console.
9. Recheck network.
10. Recheck responsive viewports.
11. Recheck locales/RTL.
12. Inspect diff.
13. Inspect status.
14. Confirm no unrelated changes.
15. Confirm temporary test values removed.
16. Confirm no debug artifacts.
17. Confirm no secrets added/exposed.

---

# 53. REPORTING DEPTH

Keep the final report concise but complete.

Do not dump:

- entire source files
- every DB row
- every API payload
- every console message
- every inspected dependency

Include enough evidence to justify production-critical conclusions.

Expand detail for:

- failures
- fixes
- security problems
- schema mismatches
- unresolved risks
- significant architecture findings

---

# 54. REQUIRED FINAL REPORT

## 1. TARGET

Report:

- supplied target
- route type
- resolved routes
- primary frontend files
- layout

---

## 2. VERIFIED ARCHITECTURE

Replace with actual project elements:

```text
Target
  ↓
Frontend Route
  ↓
Page / Components
  ↓
API Client
  ↓
Backend Route
  ↓
Middleware / Validation
  ↓
Controller
  ↓
Service / Repository
  ↓
Model
  ↓
Database
  ↑
Admin / CMS
```

---

## 3. COMPONENTS & DEPENDENCIES

Include relevant:

- frontend files
- backend files
- models
- tables
- APIs
- Admin/CMS routes
- translations
- media/storage
- shared dependencies

---

## 4. ISSUES FOUND & FIXES

Use severity only when relevant:

### Critical

### High

### Medium

### Low

For each:

```text
Problem:
Root cause:
Affected files:
Impact:
Fix:
Verification:
Regression check:
```

Do not create empty severity groups.

---

## 5. DATABASE REVIEW

Report:

```text
Environment:
Tables:
Models:
Migrations:
Actual schema:
Relationships:
Foreign keys:
Indexes:
Constraints:
Schema/code consistency:
Changes:
Verification:
```

---

## 6. BACKEND / API REVIEW

For each major endpoint:

```text
Endpoint:
Method:
Controller:
Authentication:
Authorization:
Validation:
Frontend consumer:
Runtime:
Response contract:
Locale:
Verification level:
```

---

## 7. ADMIN / CMS REVIEW

Report:

- routes/pages
- resources
- tables
- mutation architecture
- CREATE
- READ
- UPDATE
- DELETE/remove
- reorder
- status
- translation sync
- media
- public synchronization
- reversible mutation performed YES/NO
- equivalent automated evidence if applicable

---

## 8. REVERSIBLE CMS TEST

When applicable:

```text
Database environment:
Live mutation safe:
Original state recorded:
Temporary mutation:
Database reflected:
Public API reflected:
Frontend received:
Browser displayed:
Original restored:
Database restoration:
API restoration:
Browser restoration:
Temporary value remaining:
```

---

## 9. BROWSER RUNTIME

```text
Browser/tool:
Target loaded:
Console errors:
Relevant console warnings:
Uncaught exceptions:
Unhandled promise rejections:
Failed relevant network requests:
Broken assets:
Interactions:
```

For route families, include representative route runtime evidence.

---

## 10. RESPONSIVE REVIEW

| Viewport  | Status | Evidence / Issues |
| --------- | ------ | ----------------- |
| 375×812   |        |                   |
| 768×1024  |        |                   |
| 1366×768  |        |                   |
| 1920×1080 |        |                   |

Do not infer PASS from CSS inspection.

---

## 11. LOCALE / RTL

If multilingual:

| Locale        | Direction | Content | API | Layout | Status |
| ------------- | --------- | ------- | --- | ------ | ------ |
| active locale | LTR/RTL   |         |     |        |        |

Include all relevant active locales.

---

## 12. AUTHENTICATION / AUTHORIZATION

```text
Unauthenticated:
Authenticated non-admin:
Authorized admin:
```

---

## 13. SECURITY

Report only relevant confirmed findings.

Never print secrets.

---

## 14. ACCESSIBILITY

Report:

- semantic structure
- keyboard/focus
- labels
- alt text
- relevant runtime findings
- verification scope

Do not claim formal compliance without formal testing.

---

## 15. PERFORMANCE

Separate:

- confirmed issues
- fixes
- observations
- no issue found

---

## 16. SEO / MEDIA / PRODUCTION CONFIG

Report relevant:

- SEO
- media/storage
- environment-driven URLs
- CORS/Sanctum/session
- production config readiness
- hardcoded development assumptions

---

## 17. STATIC / BUILD

For each executed command:

```text
Command:
Result:
Relevant output:
Target-related warnings:
```

Do not list unexecuted commands.

---

## 18. CHANGE OWNERSHIP

### Changes Made During This Audit

List only audit-created changes.

### Pre-existing Changes

List separately.

---

## 19. PRODUCTION READINESS MATRIX

Use only:

- PASS
- FAIL
- CODE-VERIFIED
- NOT VERIFIED
- N/A

| Check                                         | Status | Evidence |
| --------------------------------------------- | ------ | -------- |
| Frontend runtime                              |        |          |
| Browser console                               |        |          |
| Browser network                               |        |          |
| Responsive design                             |        |          |
| Interactions                                  |        |          |
| Backend/API                                   |        |          |
| API contracts                                 |        |          |
| Database schema                               |        |          |
| Database relationships                        |        |          |
| Admin/CMS                                     |        |          |
| CRUD/synchronization                          |        |          |
| Reversible CMS mutation / equivalent evidence |        |          |
| Authentication                                |        |          |
| Authorization                                 |        |          |
| Validation                                    |        |          |
| i18n/RTL                                      |        |          |
| Media/storage                                 |        |          |
| Accessibility                                 |        |          |
| SEO                                           |        |          |
| Performance                                   |        |          |
| Production config mechanism                   |        |          |
| Type checking                                 |        |          |
| Lint                                          |        |          |
| Production build                              |        |          |
| Relevant tests                                |        |          |
| Admin → DB → API → Frontend → Browser         |        |          |

Add route-family-specific rows where useful.

---

## 20. REMAINING ISSUES

For each:

```text
Issue:
Why unresolved:
What was attempted:
Production impact:
Recommended action:
```

Only genuine unresolved items.

---

# 55. FINAL VERDICT

Choose exactly ONE.

## ✅ READY FOR PRODUCTION

Use only when no production blocker remains and production-critical checks have sufficient evidence.

## ⚠️ READY WITH MINOR ISSUES

Use only for genuinely non-blocking residual issues.

## ❌ NOT READY FOR PRODUCTION

Use when core functionality, security, data integrity, or deployment remains broken.

## ❓ PRODUCTION READINESS NOT FULLY VERIFIED

Use when important production-critical verification could not be completed.

No custom verdict.

No:

```text
PASS WITH NOTE
```

No optimistic shortcut.

---

# 56. AUTONOMY

You may:

- inspect repository files
- search code
- trace dependencies
- inspect configuration
- run safe diagnostics
- run tests
- run type checks
- run lint
- run builds
- inspect routes
- inspect migrations/schema
- safely inspect DB
- inspect HTTP/API responses
- inspect logs
- use existing browser/E2E tooling
- use safe installed browsers
- implement target-related fixes
- add/update relevant tests
- perform reversible non-production CMS tests
- restore temporary test data

A new development-only testing dependency may be added only when:

- genuinely required
- no suitable tool exists
- safe
- non-disruptive
- useful
- non-invasive alternatives are inadequate

Do not automatically:

- delete production data
- reset databases
- truncate important data
- drop tables
- run destructive migrations
- run destructive seeders
- expose secrets
- weaken security
- perform unrelated large refactors
- mutate confirmed production CMS data

---

# 57. NON-NEGOTIABLE RULES

1. Do not guess.
2. Read `AGENTS.md`.
3. Use `README.md` when project context is needed.
4. Treat `SKILL.md` as the page-audit protocol.
5. Start work immediately once target is clear.
6. Do not stop at frontend source.
7. Trace complete direct dependency chain.
8. Prefer runtime evidence.
9. HTTP 200 is not proof of functionality.
10. Route existence is not proof of behavior.
11. Controller existence is not proof of API correctness.
12. PUT existence is not proof of full CRUD.
13. Build success is not proof of runtime correctness.
14. CSS is not proof of responsive correctness.
15. Screenshot is not proof of console/network cleanliness.
16. Fix confirmed issues when safe.
17. Verify every meaningful fix.
18. Reopen modified files.
19. Inspect final Git diff/status.
20. Preserve architecture.
21. Do not create duplicate systems.
22. Do not hardcode database-managed content.
23. Do not weaken security or validation.
24. Do not hide errors.
25. Do not perform destructive DB operations automatically.
26. Never expose secrets.
27. Do not make unrelated changes.
28. Check shared dependency regressions.
29. Prefer minimal root-cause fixes.
30. Use project conventions.
31. Distinguish code verification from runtime verification.
32. Do not stop browser testing because Playwright is absent.
33. Exhaust reasonable safe alternatives before NOT VERIFIED.
34. Restore temporary CMS data.
35. Unknown databases are not disposable.
36. Wildcard routes must be discovered, not invented.
37. Route families require representative valid and invalid cases.
38. Do not restart an already active audit unnecessarily.
39. Invalidate only verification affected by new changes.
40. Rollback temporary mutations immediately if verification fails.
41. Equivalent automated evidence may replace a live mutation.
42. Keep security testing non-destructive.
43. Do not optimize merely for more PASS labels.
44. Leave the implementation safer and more reliable.

---

# 58. SUCCESS CONDITION

The audit is complete only when you have:

- DISCOVERED the actual architecture
- RESOLVED the target
- TRACED the dependency graph
- AUDITED frontend/backend/API/database/Admin/security/i18n/media/accessibility/SEO/performance/config
- EXECUTED the strongest safe verification available
- FIXED safe confirmed issues
- RETESTED meaningful fixes
- VERIFIED browser runtime where possible
- VERIFIED responsive behavior where possible
- VERIFIED Admin/CMS synchronization through runtime or equivalent strong evidence
- CHECKED regressions
- REVIEWED Git diff/status
- RESTORED temporary data
- REPORTED an evidence-based production verdict

The objective is not to maximize PASS labels.

The objective is to maximize **real confidence backed by evidence**.

---

# 59. INVOCATION EXAMPLES

## Exact Page

```text
Audit:
http://localhost:5173/about
```

## Route Family

```text
Audit:
http://localhost:5173/announcements/*
```

## List + Detail Family

```text
Audit these as one connected feature:

http://localhost:5173/announcements
http://localhost:5173/announcements/*
```

## Explicit Skill Reference

```text
Read and follow:

C:\Users\KAPAKA\Desktop\international.bstu.uz\SKILL.md

Target:
http://localhost:5173/about

Begin immediately.
```

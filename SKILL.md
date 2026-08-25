---
name: bstu-end-to-end-page-audit
description: >
  End-to-end audit, repair, testing, and production-readiness verification
  for BSTU International pages and route families across frontend, backend,
  APIs, database, Admin/CMS, CRUD/synchronization, authentication,
  authorization, localization, media, accessibility, SEO, security,
  performance, browser runtime, responsive behavior, configuration,
  regression safety, and deployment readiness.
version: "3.0"
---

# BSTU END-TO-END PAGE AUDIT SKILL

## 1. PURPOSE

This skill is the authoritative procedure for auditing, repairing, testing, and verifying a page or route family inside the **BSTU International Website** repository.

Typical invocations:

```text
Audit:
http://localhost:5173/about
```

```text
Audit:
http://localhost:5173/announcements/*
```

```text
Audit these as one connected feature:

http://localhost:5173/announcements
http://localhost:5173/announcements/*
```

The goal is not to review only the visible UI or produce optimistic PASS labels.

The goal is to establish, using the strongest safe evidence available, whether the target is genuinely:

- functional
- correctly connected
- database-consistent
- administratively manageable
- secure
- properly validated
- correctly localized
- responsive
- accessible
- performant
- maintainable
- production-safe
- deployment-ready

Begin immediately once the target is clear.

Do not ask the user to repeat instructions already contained in this skill.

Do not request confirmation for ordinary safe inspection, diagnosis, testing, or target-related fixes.

---

# 2. REPOSITORY CONTEXT

Expected repository root:

```text
C:\Users\KAPAKA\Desktop\international.bstu.uz
```

Expected high-level structure:

```text
international.bstu.uz/
├── apps/
│   ├── web/
│   └── api/
├── AGENTS.md
├── README.md
├── SKILL.md
├── international.bstu.uz.code-workspace
└── .gitignore
```

Before executing this skill:

1. Read and obey `AGENTS.md`.
2. Use `README.md` for durable project/developer context where needed.
3. Use this `SKILL.md` as the specialized page/route audit procedure.

Responsibilities:

```text
README.md
→ developer/project documentation

AGENTS.md
→ repository-wide agent rules and safety

SKILL.md
→ page/route audit execution protocol
```

Repository-wide architecture, database safety, security, and Git rules remain in force during the audit.

---

# 3. ROLE

Act as the project's:

- Senior Full-Stack Engineer
- Software Architect
- QA / E2E Engineer
- Database Engineer
- Security Reviewer
- Accessibility Reviewer
- Performance Engineer
- DevOps / Production Readiness Auditor

Do not prioritize a positive verdict over an accurate verdict.

---

# 4. TARGET INTERPRETATION

## Exact Page

Example:

```text
http://localhost:5173/about
```

Audit the exact route and everything directly required to operate it.

---

## Wildcard Route Family

Example:

```text
http://localhost:5173/announcements/*
```

Treat `/*` as:

> Audit the real route family below this path.

Do not open the wildcard literally in the browser.

Discover actual route definitions from the project's router.

Possible implementations might include:

```text
/announcements/:id
/announcements/:slug
```

These are examples only.

Never invent routes.

---

## Base + Wildcard

If both are supplied:

```text
http://localhost:5173/announcements
http://localhost:5173/announcements/*
```

treat them as one connected feature family.

Audit where applicable:

- index/list page
- detail routes
- search
- filters
- sorting
- pagination
- list → detail navigation
- detail → list/back navigation
- APIs
- shared components
- database relationships
- Admin/CMS
- empty states
- invalid/not-found states

Avoid duplicate work.

---

# 5. REPRESENTATIVE DYNAMIC ROUTE TESTING

For large route families, discover real routable records from the API/database.

When suitable data exists, test representative cases such as:

1. normal valid published record
2. record with optional/null fields
3. record containing media
4. record with long content
5. relevant locale variants
6. invalid/nonexistent identifier or slug
7. inactive/unpublished record where behavior matters

Do not fabricate routes when valid records can be discovered.

Representative runtime testing is acceptable when:

- the shared implementation is confirmed
- the contract is shared
- meaningful edge cases are covered

Never claim every database record was browser-tested unless that actually occurred.

---

# 6. EXECUTION MODEL

Use:

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

For every relevant subsystem:

1. Inspect the actual implementation.
2. Trace its real dependencies.
3. Execute the strongest safe verification available.
4. Verify actual runtime/data behavior.
5. Diagnose confirmed problems.
6. Apply the smallest safe root-cause fix.
7. Retest the affected path.
8. Check likely regressions.
9. Report only what evidence supports.

Never guess.

---

# 7. EVIDENCE STANDARD

Use only these verification states:

## PASS

Actually executed and successfully verified with appropriate evidence.

## CODE-VERIFIED

Implementation was fully traced and appears correct, but equivalent runtime execution could not safely or practically be completed.

## FAIL

Confirmed broken or incorrect.

## NOT VERIFIED

Insufficient evidence.

## N/A

The check genuinely does not apply.

Never convert `NOT VERIFIED` into `PASS`.

---

## Evidence Priority

Prefer:

1. real browser + API + database end-to-end verification
2. runtime integration verification
3. direct API/database execution
4. automated E2E/integration tests
5. feature/unit tests
6. production build/type/lint checks
7. code inspection
8. assumptions

Assumptions are never PASS evidence.

Before using `CODE-VERIFIED` or `NOT VERIFIED`, ask internally:

> Is another safe verification method reasonably available?

---

# 8. CONTINUATION / VERIFICATION LEDGER

During an active audit, maintain an internal verification ledger.

Track production-critical areas and the evidence supporting them.

If the audit continues after code changes:

- do not restart unnecessarily
- reuse unaffected verified evidence
- invalidate checks affected by the change
- rerun downstream checks that may have regressed
- rerun final browser/build/regression verification where applicable

Example:

If only a responsive React component changed, rerun relevant:

- lint/type checks
- production build
- browser runtime
- responsive viewports
- interactions
- locales
- shared-component regression checks

Do not rediscover unrelated database architecture without reason.

---

# 9. REQUIRED DEPENDENCY TRACE

Trace the target through the real implementation:

```text
Target URL
→ Frontend Router
→ Layout
→ Page
→ Components
→ Hooks / Context / State
→ API Client
→ API Endpoint
→ Middleware
→ Validation
→ Controller
→ Service / Repository
→ Model
→ Database
→ Admin / CMS
→ CRUD / Synchronization
→ Authentication / Authorization
→ i18n
→ Media / Storage
→ SEO
→ Production Configuration
→ Browser Runtime
```

For every important dynamic public field, identify its intended source.

Do not stop at the frontend.

---

# 10. PROJECT DISCOVERY

Confirm rather than assume:

- frontend framework/version
- backend framework/version
- router
- database
- ORM/query layer
- authentication
- roles/policies/authorization
- Admin/CMS architecture
- API architecture
- state management
- validation
- localization
- media/storage
- build tooling
- test tooling
- browser/E2E tooling
- environment/config mechanism
- package manager
- workspace structure

Use existing repository conventions.

Do not redesign working architecture without a confirmed need.

---

# 11. FRONTEND AUDIT

Inspect all target-related frontend code.

## Rendering

Verify:

- conditional rendering
- nullable values
- state synchronization
- props
- hooks/effects
- dependency arrays
- cleanup
- loading behavior
- error behavior
- hydration where applicable
- safe media rendering

## Data

Verify:

- requests
- parameters
- response parsing
- loading states
- empty states
- error states
- retries where appropriate
- race conditions
- stale state
- cancellation where relevant
- unhandled promises
- malformed payload handling

## Interactions

Actually test relevant:

- buttons
- links
- CTAs
- forms
- dropdowns
- tabs
- menus
- modals
- search
- filtering
- sorting
- pagination
- uploads
- language switcher
- external links
- list/detail navigation

No unexplained dead control is acceptable.

## Code Quality

Search relevant files for:

- dead code
- duplicate logic
- invalid imports
- unused imports
- missing dependencies
- hardcoded dynamic content
- hardcoded URLs
- mock/demo data
- temporary workarounds
- TODO/FIXME
- console/debug artifacts
- unsafe fallbacks

Fix confirmed target-related issues safely.

---

# 12. RESPONSIVE & ACCESSIBILITY VERIFICATION

## Required Viewports

When browser tooling is available, test:

```text
375 × 812
768 × 1024
1366 × 768
1920 × 1080
```

Use project-defined breakpoints only when clearly more appropriate.

Inspect the **full document**, not just the initial viewport.

Check:

- header
- navigation
- title/hero
- all main sections
- cards
- lists
- tables
- forms
- media
- modals
- pagination
- footer
- fixed/sticky elements
- late-loaded content

Look for:

- horizontal overflow
- clipped text
- overlapping content
- off-screen controls
- broken grids
- malformed cards
- image distortion
- navigation breakage
- excessive whitespace
- unreadable text
- RTL issues

Where appropriate verify:

```text
document.scrollWidth <= document.documentElement.clientWidth
```

unless horizontal scrolling is intentional.

Do not mark responsive design PASS from CSS/Tailwind inspection alone.

---

## Accessibility

Check relevant:

- semantic HTML
- heading hierarchy
- labels
- input associations
- alt text
- keyboard usability
- focus visibility
- focus order
- button/link semantics
- appropriate ARIA
- state communication not dependent on color alone

Use runtime inspection where practical.

Do not claim full WCAG compliance unless formally tested to that standard.

---

# 13. API CONTRACT & BACKEND AUDIT

For every important API used by the target, compare:

```text
Frontend expectation
↔
Actual backend response
```

Verify:

- URL
- method
- status
- content type
- field names
- types
- nullability
- nested structures
- arrays
- IDs
- pagination
- localized fields
- media URLs
- dates
- booleans
- statuses
- optional values
- error payloads
- frontend compatibility

If TypeScript or equivalent types exist, compare them with actual responses.

---

## HTTP 200 Rule

This is forbidden reasoning:

```text
HTTP 200
therefore API works
```

A `200` response containing malformed, incompatible, stale, or hidden error data is not PASS.

---

## Backend Inspection

Inspect relevant:

- routes
- middleware
- controllers
- validators
- services
- repositories
- models
- resources/serializers
- policies
- exception handling

Check:

- correct methods/statuses
- validation
- sanitization
- pagination
- filtering
- sorting
- error handling
- N+1
- repeated queries
- missing eager loading
- unbounded queries
- unsafe raw queries
- mass assignment
- sensitive field exposure

Fix confirmed issues while preserving established contracts where practical.

---

# 14. DATABASE AUDIT & SAFETY

Compare:

```text
Migration
↔ Actual Database Schema
↔ Model
↔ Service/Repository
↔ Controller
↔ API
↔ Frontend
```

Inspect applicable:

- tables
- columns
- types
- primary keys
- foreign keys
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

- schema/code drift
- missing fields
- obsolete fields
- incorrect types
- incorrect relationships
- invalid foreign keys
- missing useful indexes
- dangerous cascades
- inconsistent translations

Do not create migrations without a confirmed requirement.

---

## Environment Classification

Before any mutation determine whether the connected database is:

- development
- automated test
- staging
- production
- unknown

Never expose database credentials.

Never assume:

```text
localhost = disposable database
```

An unknown database must be treated as potentially important.

---

## Database Safety

Never automatically:

- drop tables
- reset databases
- run `migrate:fresh`
- run `db:wipe`
- truncate important data
- delete real data for convenience
- remove important columns blindly
- run destructive seeders
- rewrite migration history recklessly

Prefer:

- additive changes
- reversible migrations
- backward-compatible migrations
- safe indexes
- safe constraints

If destructive work appears required, report it rather than automatically executing it.

---

# 15. ADMIN / CMS & CRUD

This section is mandatory whenever the target depends on dynamic managed content.

Locate relevant Admin/CMS management for:

- page content
- programs
- faculties
- services
- announcements
- news
- blog
- leadership
- statistics
- contacts
- translations
- media
- documents
- SEO
- ordering
- visibility/status

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

- disconnected Admin fields
- unmanaged public DB fields
- hardcoded public values
- saved Admin values that never reach public output
- public fields with no intended management source

---

## Mutation Architecture

Do not infer CRUD from HTTP method names.

Determine whether mutations use:

- POST
- PUT
- PATCH
- DELETE
- aggregate synchronization

If an aggregate operation manages a page/resource, determine whether it:

- creates missing records
- updates existing records
- removes records
- soft-deletes
- restores
- reorders
- changes status
- synchronizes translations
- synchronizes relationships
- replaces/removes media

`PUT exists` does not mean full CRUD works.

---

## CRUD Verification

Where applicable verify:

### CREATE

- validation
- persistence
- relationships
- translations
- media
- public exposure

### READ

- Admin values
- public API values
- public frontend values

### UPDATE

- current values load
- changes persist
- translations update
- relationships remain valid
- media replacement works
- public API updates
- frontend updates

### DELETE / REMOVE

- correct record affected
- relationships remain valid
- file behavior is safe
- API reflects removal
- frontend handles removal

Also verify when applicable:

- activate/deactivate
- publish/unpublish
- reorder
- search
- filters
- pagination
- preview
- restoration

---

# 16. SAFE REVERSIBLE CMS VERIFICATION

When database safety is positively established, prefer a reversible real mutation.

Procedure:

1. Select one safe existing non-production textual CMS value.
2. Record its exact original state.
3. Create a unique temporary value:

```text
AUDIT_TEST_<timestamp>
```

4. Submit through the real authenticated Admin/application mutation path.
5. Verify mutation success.
6. Verify database.
7. Verify public API.
8. Verify frontend receives it.
9. Verify browser displays it.
10. Restore the exact original state through the real application path.
11. Verify database restoration.
12. Verify API restoration.
13. Verify frontend/browser restoration.
14. Confirm no audit value remains.

Never perform this against confirmed production data.

---

## Rollback Guarantee

Before mutation:

- capture original state
- preserve rollback information
- prepare restoration

If any later verification step fails:

1. stop normal verification
2. restore the original state immediately
3. verify restoration
4. continue diagnosis only after restoration

Temporary audit values must never be left behind because a test failed.

---

## Equivalent Strong Evidence

A live mutation is preferred but not mandatory if equivalent strong evidence exists, such as:

- isolated E2E tests
- transactional integration tests
- Laravel feature tests
- disposable test database
- isolated staging workflow

Do not downgrade a fully verified feature merely because important development data was intentionally not mutated when equivalent strong evidence exists.

---

# 17. AUTHENTICATION, AUTHORIZATION & SECURITY

Authentication and authorization are separate.

For protected Admin endpoints verify where safely practical:

```text
Unauthenticated
→ expected 401 or intentional equivalent

Authenticated non-admin
→ expected 403 or intentional equivalent

Authorized admin
→ expected successful access
```

Do not treat a 401 response as proof that role authorization works.

---

## Security Review

Check applicable:

- XSS
- CSRF
- SQL injection
- mass assignment
- unsafe HTML
- unsafe redirects
- path traversal
- file upload validation
- MIME spoofing
- excessive upload size
- sensitive data exposure
- debug output
- stack traces
- credentials
- tokens
- API keys
- secrets
- internal paths

Never print secrets.

Security testing must remain non-destructive.

Do not perform:

- credential brute force
- denial-of-service testing
- destructive fuzzing
- destructive penetration tests
- uncontrolled harmful payloads

---

# 18. MEDIA, LOCALIZATION & SEO

## Media / Storage

When relevant verify:

- upload
- validation
- MIME
- extension
- size
- naming
- storage
- public URL generation
- replacement
- deletion
- orphan handling
- missing-file fallback
- broken-image handling

Search relevant code for unsafe dependencies on:

```text
localhost
127.0.0.1
absolute Windows paths
development-only storage URLs
hardcoded local assets
```

Production must not depend on local-machine paths.

---

## i18n / Localization

Discover active locales from the real project/database.

Verify:

- translation availability
- missing translations
- fallback behavior
- DB translations
- API locale behavior
- Admin translation fields
- language switching
- locale persistence
- localized routes where applicable
- localized SEO where applicable
- LTR/RTL

For RTL languages verify browser behavior for:

- direction
- alignment
- navigation
- cards
- tables
- forms
- modals
- pagination
- directional icons
- mixed-direction content

Switch languages at runtime where possible.

---

## SEO

Verify where applicable:

- title
- meta description
- canonical
- Open Graph
- Twitter/X metadata
- robots
- structured data
- heading hierarchy
- image alt text

Inspect runtime-generated metadata where possible.

Production metadata should not accidentally reference:

- localhost
- development ports
- filesystem paths
- staging-only URLs

---

# 19. BROWSER RUNTIME VERIFICATION

Browser verification is production-critical for normal public pages.

Do not stop merely because Playwright is absent.

Check for available:

- existing E2E tooling
- Playwright
- Cypress
- Puppeteer
- Selenium
- Chrome
- Chromium
- Edge
- Firefox

Prefer a temporary/non-invasive method over permanently modifying the project for one audit.

---

## Runtime Checks

Actually load the target when browser execution is available.

Verify:

- page load
- rendering
- console errors
- relevant console warnings
- uncaught exceptions
- unhandled promise rejections
- framework warnings
- network requests
- failed fetch/XHR
- relevant 4xx/5xx
- broken resources
- broken images
- navigation
- interactions
- language switching
- route-family detail behavior

A screenshot alone is insufficient.

Report actual counts/results:

```text
Console errors:
Relevant console warnings:
Uncaught exceptions:
Unhandled promise rejections:
Failed relevant network requests:
Broken resources/assets:
```

If target-related failures exist:

1. identify root cause
2. fix safely
3. retest
4. report post-fix state

---

## Page HTTP Rule

```text
GET /target => HTTP 200
```

proves only that the server returned a successful response.

It does not prove:

- React rendering
- JS execution
- console cleanliness
- API integration
- correct content
- responsive behavior
- interactions
- localization
- accessibility

Never convert page HTTP success into frontend runtime PASS.

---

# 20. FAILURE-STATE VERIFICATION

Where safe and practical, verify relevant:

- API error
- API unavailable
- empty dataset
- missing optional values
- missing media
- broken media URL
- malformed payload
- validation error
- unauthenticated request
- forbidden request
- invalid route parameter
- nonexistent detail record
- missing translation
- slow loading

The page should fail gracefully rather than crash or become unusable.

Do not intentionally disrupt production systems.

---

# 21. PERFORMANCE & PRODUCTION CONFIGURATION

## Performance

Check meaningful target-related problems.

Frontend:

- duplicate requests
- excessive requests
- avoidable sequential calls
- unnecessary rerenders
- large bundles
- oversized media
- blocking resources
- useful lazy loading opportunities

Backend:

- N+1
- duplicate queries
- unnecessary joins
- missing useful indexes
- unbounded queries
- excessive payloads
- missing pagination
- inefficient localization loading

Do not perform speculative micro-optimizations.

---

## Production Configuration

Search relevant code/config for:

```text
localhost
127.0.0.1
hardcoded ports
hardcoded domains
hardcoded API URLs
hardcoded asset URLs
debug flags
test credentials
mock
dummy
TODO
FIXME
development fallbacks
```

Verify environment-driven configuration for:

- frontend URL
- backend/API URL
- application URL
- assets/storage
- CORS
- Sanctum
- cookies
- sessions
- SameSite
- Secure
- HTTPS
- cache
- queue
- logging
- production build behavior

Local development values such as:

```text
APP_ENV=local
APP_DEBUG=true
```

are not defects by themselves.

Do not change the developer's local environment to production mode merely to obtain PASS.

---

# 22. BUILD, TESTS & FIX POLICY

Use actual repository commands.

Run applicable:

- type checking
- lint
- frontend production build
- backend tests
- frontend tests
- feature tests
- integration tests
- E2E tests
- route inspection
- migration status
- schema verification

Classify failures as:

```text
TARGET-RELATED
```

or:

```text
PRE-EXISTING / UNRELATED
```

Do not blindly repair unrelated repository-wide issues.

---

## Production Build Rule

A successful build does not prove runtime correctness.

Inspect relevant warnings for:

- unresolved assets
- broken imports
- missing dependencies
- chunk warnings
- environment assumptions
- debug/source-map concerns
- unusually large target-related assets

Only report Build PASS if the production build actually completed successfully.

---

## Root-Cause Repair

For every confirmed issue:

1. reproduce
2. identify root cause
3. implement smallest safe correction
4. inspect affected dependencies
5. retest
6. confirm resolution
7. check regressions

Do not hide problems with:

- empty catches
- disabled validation
- unjustified `any`
- `@ts-ignore`
- `@ts-nocheck`
- disabled lint rules
- arbitrary hardcoded fallbacks
- removed authorization
- suppressed exceptions

unless the established architecture genuinely requires it.

---

# 23. MODIFIED FILE & SHARED-DEPENDENCY VERIFICATION

After changing any file:

1. reopen the final file
2. verify syntax/JSX/template structure
3. ensure no duplicate blocks
4. check imports
5. confirm new imports are used
6. confirm obsolete imports are removed
7. run relevant checks
8. run relevant tests
9. run build where appropriate
10. inspect final diff

Before modifying shared:

- components
- hooks
- contexts
- services
- models
- APIs
- middleware
- layouts
- database tables
- translations
- utilities

search for other consumers.

Do not fix one route by knowingly breaking another.

---

# 24. GIT SAFETY

Before finalizing inspect:

```text
git diff
git status
```

Separate:

## Changes Made During This Audit

from:

## Pre-existing Changes

Do not:

- claim ownership of pre-existing work
- silently revert user changes
- overwrite unrelated untracked files
- run destructive cleanup against user work

Confirm no unintended:

- audit markers
- temporary CMS values
- debug logs
- temporary scripts
- screenshots
- browser helpers
- test artifacts
- unrelated modifications

remain in tracked production code.

---

# 25. NO FALSE PASS RULE

The following are not equivalent:

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

PUT exists
≠ full CRUD verified

Migration exists
≠ actual DB schema verified

Model relationship exists
≠ referential integrity verified

Build succeeds
≠ runtime verified

Responsive CSS exists
≠ responsive runtime verified

Screenshot exists
≠ browser console/network verified

Code looks correct
≠ production ready
```

---

# 26. PRODUCTION-READY GATE

Do not return:

```text
✅ READY FOR PRODUCTION
```

unless applicable production-critical areas have sufficient evidence.

For a normal dynamic CMS-managed public page this generally includes:

- frontend runtime
- interactions
- browser console
- browser network
- responsive runtime
- API behavior
- API contracts
- actual database schema
- database relationships
- Admin/CMS management
- CRUD/synchronization
- validation
- authentication
- authorization
- localization/RTL
- media/storage
- accessibility at appropriate scope
- SEO
- performance
- production configuration
- production build
- Admin → DB → API → Frontend → Browser flow where applicable

Equivalent strong automated evidence may replace live mutation where appropriate.

If an important production-critical area remains insufficiently verified, normally use:

```text
❓ PRODUCTION READINESS NOT FULLY VERIFIED
```

---

# 27. FINAL VERIFICATION

Before reporting:

1. rerun affected static checks
2. rerun relevant tests
3. rerun production build
4. recheck affected API contracts
5. recheck database relationships
6. recheck Admin/public synchronization
7. rerun browser verification
8. recheck console/network
9. recheck responsive viewports
10. recheck relevant locales/RTL
11. inspect `git diff`
12. inspect `git status`
13. confirm no unrelated changes
14. confirm temporary audit values are gone
15. confirm no debug artifacts remain
16. confirm no secrets were added or exposed

---

# 28. REPORTING RULE

Keep the final report concise but complete.

Do not dump:

- entire source files
- every database row
- every API payload
- every console message
- every inspected dependency

Include enough evidence to justify every production-critical conclusion.

Expand detail mainly for:

- failures
- fixes
- security issues
- schema mismatches
- unresolved risks
- important architecture findings

---

# 29. REQUIRED FINAL REPORT

## 1. TARGET

Report:

```text
Supplied target:
Route type:
Resolved routes:
Primary frontend files:
Layout:
```

---

## 2. VERIFIED ARCHITECTURE

Replace this generic diagram with actual project elements:

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

List relevant:

- frontend files
- backend files
- models
- tables
- APIs
- Admin/CMS pages
- translation sources
- media/storage dependencies
- shared dependencies

---

## 4. ISSUES FOUND & FIXES

Use severity only where issues exist:

### Critical

### High

### Medium

### Low

For each issue:

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
Locale behavior:
Verification level:
```

Do not use HTTP status alone as verification.

---

## 7. ADMIN / CMS REVIEW

Report:

- routes/pages
- managed resources
- tables
- mutation architecture
- CREATE
- READ
- UPDATE
- DELETE/remove
- reorder
- status
- translations
- media
- public synchronization
- reversible mutation performed: YES/NO
- equivalent automated evidence where applicable

---

## 8. REVERSIBLE CMS TEST

When applicable:

```text
Database environment:
Live mutation safe:
Original state recorded:
Temporary mutation sent:
Database reflected:
Public API reflected:
Frontend received:
Browser displayed:
Original state restored:
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
Broken resources/assets:
Interactions:
```

For route families include representative detail-route evidence.

---

## 10. RESPONSIVE REVIEW

| Viewport  | Status | Evidence / Issues |
| --------- | ------ | ----------------- |
| 375×812   |        |                   |
| 768×1024  |        |                   |
| 1366×768  |        |                   |
| 1920×1080 |        |                   |

Do not infer PASS from source/CSS inspection.

---

## 11. LOCALE / RTL REVIEW

When multilingual:

| Locale        | Direction | Content | API | Layout | Status |
| ------------- | --------- | ------- | --- | ------ | ------ |
| active locale | LTR/RTL   |         |     |        |        |

Include relevant active locales.

---

## 12. AUTHENTICATION / AUTHORIZATION

```text
Unauthenticated:
Authenticated non-admin:
Authorized admin:
```

---

## 13. SECURITY / ACCESSIBILITY

Report confirmed relevant findings.

Never print secrets.

State accessibility verification scope accurately.

---

## 14. PERFORMANCE / SEO / MEDIA / PRODUCTION CONFIG

Report:

- confirmed performance findings
- SEO status
- media/storage status
- production configuration readiness
- hardcoded development assumptions
- CORS/Sanctum/session concerns where relevant

---

## 15. STATIC / BUILD VERIFICATION

For each important executed command:

```text
Command:
Result:
Relevant output:
Target-related warnings:
```

Do not list commands that were not executed.

---

## 16. CHANGE OWNERSHIP

### Changes Made During This Audit

List only audit-created changes.

### Pre-existing Changes

List separately.

---

## 17. PRODUCTION READINESS MATRIX

Use only:

- PASS
- FAIL
- CODE-VERIFIED
- NOT VERIFIED
- N/A

| Check                                 | Status | Evidence |
| ------------------------------------- | ------ | -------- |
| Frontend runtime                      |        |          |
| Interactions                          |        |          |
| Browser console                       |        |          |
| Browser network                       |        |          |
| Responsive design                     |        |          |
| Backend/API                           |        |          |
| API contracts                         |        |          |
| Database schema                       |        |          |
| Database relationships                |        |          |
| Admin/CMS                             |        |          |
| CRUD/synchronization                  |        |          |
| CMS mutation / equivalent evidence    |        |          |
| Authentication                        |        |          |
| Authorization                         |        |          |
| Validation                            |        |          |
| i18n/RTL                              |        |          |
| Media/storage                         |        |          |
| Accessibility                         |        |          |
| SEO                                   |        |          |
| Performance                           |        |          |
| Production configuration              |        |          |
| Type checking                         |        |          |
| Lint                                  |        |          |
| Production build                      |        |          |
| Relevant tests                        |        |          |
| Admin → DB → API → Frontend → Browser |        |          |

Add route-family-specific rows where useful.

---

## 18. REMAINING ISSUES

For each genuine unresolved issue:

```text
Issue:
Why unresolved:
What was attempted:
Production impact:
Recommended action:
```

---

# 30. FINAL VERDICT

Choose exactly ONE:

## ✅ READY FOR PRODUCTION

Use only when no production blocker remains and applicable production-critical areas have sufficient evidence.

## ⚠️ READY WITH MINOR ISSUES

Use only when remaining issues are genuinely non-blocking and do not affect core functionality, security, data integrity, or deployment reliability.

## ❌ NOT READY FOR PRODUCTION

Use when core functionality, security, data integrity, build, or deployment remains broken.

## ❓ PRODUCTION READINESS NOT FULLY VERIFIED

Use when important production-critical verification could not be completed.

No custom verdict.

No `PASS WITH NOTE`.

No optimistic shortcut.

---

# 31. AUTONOMY & SAFETY

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
- safely inspect database structure/content
- inspect HTTP/API responses
- inspect runtime logs
- use existing E2E/browser tooling
- use safe installed browsers
- implement target-related fixes
- add/update relevant tests
- perform reversible non-production CMS verification
- restore temporary test data

A new development-only verification dependency may be added only when:

- genuinely required
- no suitable existing tool exists
- safe
- non-disruptive
- useful
- a temporary/non-invasive alternative is inadequate

Do not automatically:

- delete production data
- reset databases
- truncate important data
- drop important tables
- run destructive migrations
- run destructive seeders
- expose secrets
- weaken authentication/authorization
- weaken validation
- perform unrelated large-scale refactors
- mutate confirmed production CMS data

---

# 32. NON-NEGOTIABLE RULES

1. Do not guess.
2. Read `AGENTS.md`.
3. Use `README.md` when project context is needed.
4. Treat `SKILL.md` as the audit protocol.
5. Start immediately once the target is clear.
6. Do not stop at frontend source.
7. Trace the complete direct dependency chain.
8. Prefer runtime evidence.
9. HTTP 200 is not proof of functionality.
10. API 200 is not proof of contract correctness.
11. Route/controller existence is not proof of behavior.
12. PUT existence is not proof of full CRUD.
13. Migration existence is not proof of actual schema.
14. Build success is not proof of runtime correctness.
15. Responsive CSS is not proof of responsive runtime.
16. A screenshot is not proof of clean console/network.
17. Fix confirmed issues when safe.
18. Verify every meaningful fix.
19. Reopen modified files.
20. Inspect final Git diff/status.
21. Preserve established architecture.
22. Do not create duplicate systems.
23. Do not hardcode database-managed public content.
24. Do not weaken security or validation.
25. Do not hide errors instead of fixing them.
26. Never automatically perform destructive database operations.
27. Never expose secrets.
28. Do not make unrelated changes.
29. Check regression impact of shared changes.
30. Prefer minimal root-cause fixes.
31. Use repository conventions.
32. Distinguish code verification from runtime verification.
33. Do not stop browser verification merely because Playwright is absent.
34. Exhaust reasonable safe alternatives before `NOT VERIFIED`.
35. Restore all temporary CMS/audit data.
36. Unknown databases are not disposable.
37. Wildcard routes must be discovered, not invented.
38. Route families require representative valid and invalid cases.
39. Do not restart an active audit unnecessarily.
40. Invalidate only verification affected by subsequent changes.
41. Roll back temporary mutations immediately if verification fails.
42. Equivalent strong automated evidence may replace live mutation.
43. Keep security testing non-destructive.
44. Do not optimize merely to increase PASS counts.
45. Leave the target and repository safer and more reliable than you found them.

---

# 33. SUCCESS CONDITION

The audit is complete only when you have:

- discovered the real target architecture
- resolved exact/dynamic routes
- traced the relevant dependency graph
- audited frontend/backend/API/database/Admin/security/i18n/media/accessibility/SEO/performance/configuration
- executed the strongest safe verification reasonably available
- fixed safe confirmed issues
- retested meaningful changes
- verified browser runtime where reasonably possible
- verified responsive behavior where reasonably possible
- verified CMS synchronization through runtime or equivalent strong evidence where applicable
- checked regression impact
- reviewed Git diff/status
- restored temporary data
- reported an evidence-based production verdict

The goal is not to maximize PASS labels.

The goal is to maximize **real confidence backed by evidence**.

---

# 34. INVOCATION EXAMPLES

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

## Connected List + Detail Family

```text
Audit these as one connected feature:

http://localhost:5173/announcements
http://localhost:5173/announcements/*
```

## Explicit Reference

```text
Read and follow:

C:\Users\KAPAKA\Desktop\international.bstu.uz\SKILL.md

Target:
http://localhost:5173/about

Begin immediately.
```

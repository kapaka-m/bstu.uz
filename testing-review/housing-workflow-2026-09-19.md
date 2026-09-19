# Housing Workflow - 2026-09-19

## Implemented Rules

- Housing is not completed when no housing request exists.
- A new request requires issued/approved admission, active/issued enrollment (Spravka), issued/completed Prikaz, approved/issued/completed visa, and an approved contract-advance receipt covering at least 30% of a positive contract total in the same currency. Exactly 30% is accepted; duplicate receipt amounts are not summed.
- The initial request includes a 14-digit PINFL/JSHSHIR and the first calendar month's receipt for USD 40. The first month defaults to the server's current month and is retained during corrections.
- Administration must download/review the receipt and explicitly attest that the PINFL was verified before approving housing. Identifier format validation is not government identity verification, and receipt approval is not bank reconciliation.
- Each subsequent calendar month is tracked separately after housing approval. Outstanding months appear on the housing page and on the student dashboard. Historical housing approval is distinct from current monthly payment compliance.
- Pending requests only expose the first-month obligation. Rejected/not-required housing does not accrue displayed monthly obligations. Existing legacy approvals without new evidence are not considered completed; records are not silently deleted or approved.

## Data and Security

- Additive migration extends `housing_requests` and creates `housing_payments`; no existing application or payment data was reset.
- Each monthly receipt attempt has a version, status, reviewer, review time and rejection reason. Rejected attempts remain available; pending/approved attempts cannot be overwritten.
- Server-side rules set USD 40, require supported file MIME types and a maximum size of 10 MiB, and prevent early/duplicate uploads. Files stay on the private local storage disk.
- Student downloads are scoped to application ownership; admin routes retain Sanctum and role middleware. File paths are hidden from payment JSON.
- Application-row transaction locks serialize housing submission and review. A failed upload transaction removes only its newly stored file.
- Raw request-body debug logging was removed from locale middleware to avoid recording PINFL and file contents.
- New UI and domain-error text uses the existing four-language database translation system. Notification keys render in the student's selected language.

## Verification

- Final checks: 16 backend tests / 138 assertions, four frontend tests, ESLint, production build, scoped PHP Pint and `git diff --check` passed. All 1,754 scanned literal translation references resolved in all four locales.
- Backend feature tests use isolated in-memory SQLite and fake storage, not student data. Coverage includes missing prerequisites, 29.99% vs 30%, forged amount/status fields, PINFL/file validation, missing files, ownership and role checks, approval order, rejected receipt replacement, legacy requests, and advancing the clock for monthly tracking.
- Browser verification uses isolated synthetic student/admin API responses with live database translations. It covers four locales, widths 375/1366, blocked requests, multipart submission, receipt approval, PINFL confirmation, housing approval and the following month's form. No real student account was mutated.
- Private production storage, real payment authenticity and government PINFL validation are not simulated or certified by these checks; administration remains responsible for these reviews.

## Deployment

Apply the two `2026_09_19_000003` / `000004` migrations after backing up important data, then rebuild the web application. The local migrations and interface translation seed were applied during this task. Do not roll back the housing schema on a populated system without preserving its receipt history first.

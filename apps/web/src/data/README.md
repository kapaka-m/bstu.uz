# Frontend Data Folder

This folder is not a production content source.

`translations.js` remains here only as a technical fallback for `LocaleContext`
when the Laravel translations API is unavailable during local development. Public
website, academic, media, menu, page, and mobile-visible content must come from
the Laravel API/MySQL database and be managed through apanel.

Legacy React content captures were moved to:

```text
scripts/import-react-content/legacy-react-data
```

Use `scripts/import-react-content/import.js` only when intentionally
regenerating seed JSON from those historical captures.

# Legacy React Data Archive

These files are historical React content captures used only by the manual import
utility at `scripts/import-react-content/import.js`.

They are not imported by the production React website. Runtime public content is
served by Laravel API endpoints backed by MySQL and managed through apanel.

The import utility reads these files and writes reviewed JSON seed inputs to:

```text
apps/api/database/data
```

After running the importer, review the generated JSON diff before running any
seeders.

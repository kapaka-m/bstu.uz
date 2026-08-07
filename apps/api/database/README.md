# Database Layout

This directory is generated from the current `bstu_international` database and is organized so a disposable database can be rebuilt with:

```bash
php artisan migrate:fresh --seed
```

## Structure

- `migrations/` creates the database schema in ordered groups.
- `seeders/` loads the project data in the same ordered groups.
- `data/schema_groups/` stores SQL schema files used by the migrations.
- `data/seed_groups/` stores SQL data files used by the seeders.
- `data/database_layout.json` documents the table groups and their seeders.
- `data/database_layout_verification.json` stores the latest comparison result against the source database.
- `factories/` keeps Laravel model factories.

## Source Of Truth

The source database for this layout is `bstu_international`.

Laravel owns the `migrations` bookkeeping table. Project/application tables are recreated and seeded from the grouped schema and seed files.

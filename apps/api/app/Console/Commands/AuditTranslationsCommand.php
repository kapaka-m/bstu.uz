<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuditTranslationsCommand extends Command
{
    protected $signature = 'content:audit-translations {--json : Output record and field identifiers as JSON}';

    protected $description = 'Read-only audit of four-language CMS completeness and known untranslated text';

    public function handle(): int
    {
        $dictionary = json_decode(file_get_contents(database_path('data/localization_repairs.json')), true, flags: JSON_THROW_ON_ERROR);
        $issues = [];
        $tables = 0;
        $records = 0;
        foreach (Schema::getTableListing() as $table) {
            if (! str_ends_with($table, '_translations') && ! str_ends_with($table, 'translation_values')) {
                continue;
            }
            $columns = Schema::getColumnListing($table);
            if (! in_array('locale', $columns, true)) {
                continue;
            }
            $foreign = collect($columns)->first(fn ($column) => str_ends_with($column, '_id'));
            if (! $foreign) {
                continue;
            }
            $tables++;
            $fields = array_diff($columns, ['id', $foreign, 'locale', 'created_at', 'updated_at']);
            $groups = DB::table($table)->get()->groupBy($foreign);
            // Include parents without any translation rows, not only partially translated parents.
            foreach (Schema::getForeignKeys($table) as $key) {
                if ($key['columns'] === [$foreign]) {
                    foreach (DB::table($key['foreign_table'])->pluck($key['foreign_columns'][0]) as $id) {
                        if (! $groups->has($id)) {
                            $groups->put($id, collect());
                        }
                    }
                    break;
                }
            }
            foreach ($groups as $id => $rows) {
                $records++;
                $english = $rows->firstWhere('locale', 'en');
                foreach (['en', 'uz', 'ru', 'ar'] as $locale) {
                    $row = $rows->firstWhere('locale', $locale);
                    if (! $row) {
                        $issues[] = compact('table', 'id', 'locale') + ['field' => '*', 'reason' => 'missing_locale'];

                        continue;
                    }
                    foreach ($fields as $field) {
                        $source = $english?->$field;
                        if (filled($source) && blank($row->$field)) {
                            $issues[] = compact('table', 'id', 'locale', 'field') + ['reason' => 'missing_field'];
                        }
                        if ($locale === 'en' || ! is_string($row->$field)) {
                            continue;
                        }
                        $decoded = json_decode($row->$field, true);
                        $values = is_array($decoded) ? Arr::dot($decoded) : [$field => $row->$field];
                        foreach ($values as $path => $value) {
                            if (! is_string($value)) {
                                continue;
                            }
                            $expected = $dictionary[$value][array_search($locale, ['uz', 'ru', 'ar'], true)] ?? $value;
                            if ($expected !== $value || preg_match('/ serves as |Standard course covering topics in |^(?:Monday-Friday|Monday-Saturday|Daily) \d/', $value)) {
                                $issues[] = compact('table', 'id', 'locale', 'field') + ['path' => $path, 'reason' => 'untranslated_text'];
                            }
                        }
                    }
                }
            }
        }
        if ($this->option('json')) {
            $this->line(json_encode(compact('tables', 'records', 'issues'), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            $this->info("Scanned {$tables} translation tables and {$records} records; ".count($issues).' issues.');
            if ($issues) {
                $this->table(['Table', 'Record', 'Locale', 'Field', 'Reason'], array_map(fn ($issue) => [
                    $issue['table'], $issue['id'], $issue['locale'], $issue['field'], $issue['reason'],
                ], array_slice($issues, 0, 30)));
                $this->line('Use --json for all identifiers. No content is changed by this command.');
            }
        }

        return $issues ? self::FAILURE : self::SUCCESS;
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('staff_profile_translations')) {
            return;
        }

        DB::table('staff_profile_translations')
            ->where(function ($query) {
                foreach (['full_name', 'position', 'bio'] as $column) {
                    $query
                        ->orWhere($column, 'like', '%/strong%')
                        ->orWhere($column, 'like', '%<strong%')
                        ->orWhere($column, 'like', '%strong>%')
                        ->orWhere($column, 'like', '%/стронг%')
                        ->orWhere($column, 'like', '%стронг>%')
                        ->orWhere($column, 'like', '%/سترونغ%')
                        ->orWhere($column, 'like', '%سترونغ>%');
                }
            })
            ->orderBy('id')
            ->get()
            ->each(function ($row) {
                $updates = [];

                foreach (['full_name', 'position', 'bio'] as $column) {
                    $cleaned = $this->cleanText($row->{$column});
                    if ($cleaned !== $row->{$column}) {
                        $updates[$column] = $cleaned;
                    }
                }

                if ($updates !== []) {
                    DB::table('staff_profile_translations')
                        ->where('id', $row->id)
                        ->update($updates + ['updated_at' => now()]);
                }
            });

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        //
    }

    private function cleanText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $cleaned = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $cleaned = strip_tags($cleaned);
        $cleaned = preg_replace('/\\s*\\/?(?:strong|стронг|سترونغ)\\s*>/iu', '', $cleaned);
        $cleaned = preg_replace('/<\\s*\\/?(?:strong|стронг|سترونغ)[^>]*>/iu', '', $cleaned);
        $cleaned = preg_replace('/\\s{2,}/u', ' ', $cleaned);
        $cleaned = trim($cleaned ?? '');

        $cleaned = preg_replace('/\\bPhd\\b/u', 'PhD', $cleaned);
        $cleaned = preg_replace('/\\bassistent\\b/iu', 'Assistant', $cleaned);

        return Str::of($cleaned)->trim()->toString();
    }
};

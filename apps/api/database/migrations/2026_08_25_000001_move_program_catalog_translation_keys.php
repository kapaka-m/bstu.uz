<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->moveKeys([
            ['home', 'programs.tag', 'programs', 'catalog.tag'],
            ['home', 'programs.title', 'programs', 'catalog.title'],
            ['home', 'programs.explore', 'programs', 'catalog.explore'],
            ['home', 'programs.viewAll', 'programs', 'catalog.viewAll'],
            ['home', 'programs.degrees.bachelor', 'programs', 'degrees.bachelor'],
            ['home', 'programs.degrees.master', 'programs', 'degrees.master'],
            ['home', 'programs.degrees.phd', 'programs', 'degrees.phd'],
        ]);
    }

    public function down(): void
    {
        $this->moveKeys([
            ['programs', 'catalog.tag', 'home', 'programs.tag'],
            ['programs', 'catalog.title', 'home', 'programs.title'],
            ['programs', 'catalog.explore', 'home', 'programs.explore'],
            ['programs', 'catalog.viewAll', 'home', 'programs.viewAll'],
            ['programs', 'degrees.bachelor', 'home', 'programs.degrees.bachelor'],
            ['programs', 'degrees.master', 'home', 'programs.degrees.master'],
            ['programs', 'degrees.phd', 'home', 'programs.degrees.phd'],
        ]);
    }

    private function moveKeys(array $mappings): void
    {
        foreach ($mappings as [$fromGroup, $fromKey, $toGroup, $toKey]) {
            $source = DB::table('translation_keys')
                ->where('group', $fromGroup)
                ->where('key', $fromKey)
                ->first();

            if (! $source) {
                continue;
            }

            $targetId = DB::table('translation_keys')
                ->where('group', $toGroup)
                ->where('key', $toKey)
                ->value('id');

            if ($targetId) {
                DB::table('translation_values')
                    ->where('translation_key_id', $source->id)
                    ->update(['translation_key_id' => $targetId]);

                DB::table('translation_keys')->where('id', $source->id)->delete();
            } else {
                DB::table('translation_keys')->where('id', $source->id)->update([
                    'group' => $toGroup,
                    'key' => $toKey,
                    'description' => 'Programs catalog UI label: '.$toGroup.'.'.$toKey,
                    'updated_at' => now(),
                ]);
            }
        }

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }
};

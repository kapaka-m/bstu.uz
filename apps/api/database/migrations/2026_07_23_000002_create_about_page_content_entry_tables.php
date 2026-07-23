<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('about_page_content_entry_translations');
        Schema::dropIfExists('about_page_content_entries');

        Schema::create('about_page_content_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('about_page_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('value_type', 30)->default('text');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['about_page_id', 'path']);
        });

        Schema::create('about_page_content_entry_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('about_page_content_entry_id');
            $table->string('locale', 5);
            $table->longText('value')->nullable();
            $table->timestamps();

            $table->foreign('about_page_content_entry_id', 'about_content_entry_fk')
                ->references('id')
                ->on('about_page_content_entries')
                ->cascadeOnDelete();
            $table->unique(['about_page_content_entry_id', 'locale'], 'about_content_entry_locale_unique');
        });

        $this->migrateExistingJsonContent();

        DB::table('about_page_translations')->update([
            'content' => null,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('about_page_content_entry_translations');
        Schema::dropIfExists('about_page_content_entries');
    }

    private function migrateExistingJsonContent(): void
    {
        if (! Schema::hasTable('about_page_translations')) {
            return;
        }

        $flatten = function (array $content, string $prefix = '') use (&$flatten): array {
            $flat = [];

            foreach ($content as $key => $value) {
                $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;

                if (is_array($value) && $value !== []) {
                    $flat += $flatten($value, $path);
                    continue;
                }

                if (is_array($value)) {
                    continue;
                }

                $flat[$path] = $value;
            }

            return $flat;
        };

        $detectType = function (mixed $value): string {
            if (is_bool($value)) {
                return 'boolean';
            }

            if (is_int($value) || is_float($value)) {
                return 'number';
            }

            return 'text';
        };

        $toStorage = function (mixed $value): ?string {
            if ($value === null) {
                return null;
            }

            if (is_bool($value)) {
                return $value ? '1' : '0';
            }

            return (string) $value;
        };

        $sortOrders = [];

        DB::table('about_page_translations')
            ->orderBy('about_page_id')
            ->orderBy('locale')
            ->chunkById(50, function ($translations) use ($flatten, $detectType, $toStorage, &$sortOrders) {
                foreach ($translations as $translation) {
                    $content = json_decode($translation->content ?: '[]', true);
                    if (! is_array($content)) {
                        continue;
                    }

                    foreach ($flatten($content) as $path => $value) {
                        $sortOrders[$translation->about_page_id] ??= 0;

                        $entry = DB::table('about_page_content_entries')
                            ->where('about_page_id', $translation->about_page_id)
                            ->where('path', $path)
                            ->first();

                        if (! $entry) {
                            $entryId = DB::table('about_page_content_entries')->insertGetId([
                                'about_page_id' => $translation->about_page_id,
                                'path' => $path,
                                'value_type' => $detectType($value),
                                'sort_order' => $sortOrders[$translation->about_page_id]++,
                                'is_active' => true,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        } else {
                            $entryId = $entry->id;
                        }

                        DB::table('about_page_content_entry_translations')->updateOrInsert(
                            [
                                'about_page_content_entry_id' => $entryId,
                                'locale' => $translation->locale,
                            ],
                            [
                                'value' => $toStorage($value),
                                'updated_at' => now(),
                                'created_at' => now(),
                            ]
                        );
                    }
                }
            });
    }
};

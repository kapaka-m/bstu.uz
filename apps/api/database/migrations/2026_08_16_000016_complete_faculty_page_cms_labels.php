<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $fields = [
        'not_found_title_label',
        'not_found_description',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('faculty_page_setting_translations')) {
            return;
        }

        Schema::table('faculty_page_setting_translations', function (Blueprint $table) {
            foreach ($this->fields as $field) {
                if (! Schema::hasColumn('faculty_page_setting_translations', $field)) {
                    $table->text($field)->nullable();
                }
            }
        });

        $this->seedMissingLabels();
    }

    public function down(): void
    {
        if (! Schema::hasTable('faculty_page_setting_translations')) {
            return;
        }

        Schema::table('faculty_page_setting_translations', function (Blueprint $table) {
            foreach (array_reverse($this->fields) as $field) {
                if (Schema::hasColumn('faculty_page_setting_translations', $field)) {
                    $table->dropColumn($field);
                }
            }
        });
    }

    private function seedMissingLabels(): void
    {
        $settingId = DB::table('faculty_page_settings')->where('key', 'main')->value('id');

        if (! $settingId) {
            $settingId = DB::table('faculty_page_settings')->insertGetId([
                'key' => 'main',
                'is_active' => true,
                'settings' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($this->defaults() as $locale => $labels) {
            $row = DB::table('faculty_page_setting_translations')
                ->where('faculty_page_setting_id', $settingId)
                ->where('locale', $locale)
                ->first();

            if (! $row) {
                DB::table('faculty_page_setting_translations')->insert($labels + [
                    'faculty_page_setting_id' => $settingId,
                    'locale' => $locale,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                continue;
            }

            $missing = [];
            foreach ($labels as $field => $value) {
                if (($row->{$field} ?? null) === null || trim((string) $row->{$field}) === '') {
                    $missing[$field] = $value;
                }
            }

            if ($missing !== []) {
                DB::table('faculty_page_setting_translations')
                    ->where('id', $row->id)
                    ->update($missing + ['updated_at' => now()]);
            }
        }
    }

    private function defaults(): array
    {
        return [
            'en' => [
                'not_found_title_label' => 'Faculty Not Found',
                'not_found_description' => 'The requested faculty page could not be found.',
            ],
            'uz' => [
                'not_found_title_label' => 'Fakultet topilmadi',
                'not_found_description' => 'So‘ralgan fakultet sahifasi topilmadi.',
            ],
            'ru' => [
                'not_found_title_label' => 'Факультет не найден',
                'not_found_description' => 'Запрошенная страница факультета не найдена.',
            ],
            'ar' => [
                'not_found_title_label' => 'لم يتم العثور على الكلية',
                'not_found_description' => 'تعذر العثور على صفحة الكلية المطلوبة.',
            ],
        ];
    }
};

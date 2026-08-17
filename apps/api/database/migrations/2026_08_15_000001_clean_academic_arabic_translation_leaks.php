<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->cleanStaffOffices();
        $this->cleanArabicContentSections('faculty_translations');
        $this->cleanArabicContentSections('department_translations');

        Cache::forever('public_content_cache_version', (string) now()->getTimestamp());
    }

    public function down(): void
    {
        // Intentionally not reversible: this migration fixes localized Arabic content.
    }

    private function cleanStaffOffices(): void
    {
        if (! Schema::hasTable('staff_profile_translations')) {
            return;
        }

        DB::table('staff_profile_translations')
            ->where('locale', 'ar')
            ->orderBy('id')
            ->select(['id', 'office'])
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $office = trim((string) $row->office);
                    if ($office === '') {
                        continue;
                    }

                    $fixed = $this->arabicOffice($office);
                    if ($fixed !== $office) {
                        DB::table('staff_profile_translations')->where('id', $row->id)->update([
                            'office' => $fixed,
                            'updated_at' => now(),
                        ]);
                    }
                }
            });
    }

    private function arabicOffice(string $office): string
    {
        $value = trim($office);
        $timePattern = '([0-2]?\d:[0-5]\d\s*[-–]\s*[0-2]?\d:[0-5]\d)';

        if (preg_match('/(?:daily|يومياً)\s+'.$timePattern.'.*?(?:except|ما عدا).*?(?:monday|الاثنين).*?(?:saturday|السبت)/iu', $value, $matches)) {
            return 'يومياً '.$matches[1].' (ما عدا الاثنين والسبت)';
        }

        if (preg_match('/(?:daily|يومياً)\s+'.$timePattern.'/iu', $value, $matches)) {
            return 'يومياً '.$matches[1];
        }

        if (preg_match('/(?:monday|الاثنين)\s*[-–]\s*(?:friday|الجمعة)\s+'.$timePattern.'/iu', $value, $matches)) {
            return 'الاثنين-الجمعة '.$matches[1];
        }

        return str_ireplace(
            ['except', 'Monday', 'Friday', 'Saturday', 'Daily'],
            ['ما عدا', 'الاثنين', 'الجمعة', 'السبت', 'يومياً'],
            $value
        );
    }

    private function cleanArabicContentSections(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'content_sections')) {
            return;
        }

        DB::table($table)
            ->where('locale', 'ar')
            ->whereNotNull('content_sections')
            ->orderBy('id')
            ->select(['id', 'content_sections'])
            ->chunkById(100, function ($rows) use ($table) {
                foreach ($rows as $row) {
                    $content = (string) $row->content_sections;
                    $fixed = $this->arabicSectionText($content);

                    if ($fixed !== $content) {
                        DB::table($table)->where('id', $row->id)->update([
                            'content_sections' => $fixed,
                            'updated_at' => now(),
                        ]);
                    }
                }
            });
    }

    private function arabicSectionText(string $content): string
    {
        $replacements = [
            'Office hours:' => 'ساعات الاستقبال:',
            'Reception time:' => 'وقت الاستقبال:',
            'Phone:' => 'الهاتف:',
            'Email:' => 'البريد الإلكتروني:',
            "Bachelor's Degree" => 'درجة البكالوريوس',
            "Bachelor's degree" => 'درجة البكالوريوس',
            "Master's Degree" => 'درجة الماجستير',
            "Master's degree" => 'درجة الماجستير',
            'Head of Department' => 'رئيس القسم',
            'Faculty member' => 'عضو هيئة تدريس',
            'Department Structure' => 'هيكل القسم',
            'Prepared Specialists' => 'التخصصات التي يعدها القسم',
            'Taught Subjects' => 'المواد التي تدرس في القسم',
            'Research Work' => 'الأعمال البحثية',
            'Professor-Teachers of the Department' => 'أعضاء هيئة التدريس في القسم',
            'Monday-Friday' => 'الاثنين-الجمعة',
            'Monday–Friday' => 'الاثنين-الجمعة',
            'Daily' => 'يومياً',
            'except Monday and Saturday' => 'ما عدا الاثنين والسبت',
            'except الاثنين و السبت' => 'ما عدا الاثنين والسبت',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }
};

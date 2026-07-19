<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('web_footers', 'admissions_apply_url')) {
            Schema::table('web_footers', function (Blueprint $table) {
                $table->string('admissions_apply_url')->nullable()->after('social_links');
            });
        }

        $columns = [
            'admissions_badge' => ['string', 'description'],
            'admissions_heading' => ['string', 'admissions_badge'],
            'admissions_description' => ['text', 'admissions_heading'],
            'admissions_button_label' => ['string', 'admissions_description'],
            'newsletter_title' => ['string', 'admissions_button_label'],
            'newsletter_description' => ['text', 'newsletter_title'],
            'newsletter_placeholder' => ['string', 'newsletter_description'],
            'newsletter_success_message' => ['string', 'newsletter_placeholder'],
        ];

        foreach ($columns as $column => [$type, $after]) {
            if (! Schema::hasColumn('web_footer_translations', $column)) {
                Schema::table('web_footer_translations', function (Blueprint $table) use ($column, $type, $after) {
                    $table->{$type}($column)->nullable()->after($after);
                });
            }
        }
    }

    public function down(): void
    {
        foreach ([
            'newsletter_success_message',
            'newsletter_placeholder',
            'newsletter_description',
            'newsletter_title',
            'admissions_button_label',
            'admissions_description',
            'admissions_heading',
            'admissions_badge',
        ] as $column) {
            if (Schema::hasColumn('web_footer_translations', $column)) {
                Schema::table('web_footer_translations', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }

        if (Schema::hasColumn('web_footers', 'admissions_apply_url')) {
            Schema::table('web_footers', function (Blueprint $table) {
                $table->dropColumn('admissions_apply_url');
            });
        }
    }
};

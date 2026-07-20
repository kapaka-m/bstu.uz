<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('administration_setting_translations', function (Blueprint $table) {
            if (! Schema::hasColumn('administration_setting_translations', 'profile_category_label')) {
                $table->string('profile_category_label')->nullable()->after('structure_title');
            }
            if (! Schema::hasColumn('administration_setting_translations', 'email_address_label')) {
                $table->string('email_address_label')->nullable()->after('profile_category_label');
            }
            if (! Schema::hasColumn('administration_setting_translations', 'phone_number_label')) {
                $table->string('phone_number_label')->nullable()->after('email_address_label');
            }
            if (! Schema::hasColumn('administration_setting_translations', 'office_hours_label')) {
                $table->string('office_hours_label')->nullable()->after('phone_number_label');
            }
            if (! Schema::hasColumn('administration_setting_translations', 'academic_rank_label')) {
                $table->string('academic_rank_label')->nullable()->after('office_hours_label');
            }
            if (! Schema::hasColumn('administration_setting_translations', 'biography_label')) {
                $table->string('biography_label')->nullable()->after('academic_rank_label');
            }
            if (! Schema::hasColumn('administration_setting_translations', 'duties_label')) {
                $table->string('duties_label')->nullable()->after('biography_label');
            }
            if (! Schema::hasColumn('administration_setting_translations', 'achievements_label')) {
                $table->string('achievements_label')->nullable()->after('duties_label');
            }
        });
    }

    public function down(): void
    {
        Schema::table('administration_setting_translations', function (Blueprint $table) {
            foreach ([
                'achievements_label',
                'duties_label',
                'biography_label',
                'academic_rank_label',
                'office_hours_label',
                'phone_number_label',
                'email_address_label',
                'profile_category_label',
            ] as $column) {
                if (Schema::hasColumn('administration_setting_translations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

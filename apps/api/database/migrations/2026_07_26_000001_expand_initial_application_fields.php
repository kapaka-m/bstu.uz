<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('student_profiles', 'full_name_english')) {
                $table->string('full_name_english')->nullable()->after('user_id');
            }
            if (! Schema::hasColumn('student_profiles', 'country_of_birth')) {
                $table->string('country_of_birth')->nullable()->after('birth_date');
            }
            if (! Schema::hasColumn('student_profiles', 'place_of_birth')) {
                $table->string('place_of_birth')->nullable()->after('country_of_birth');
            }
            if (! Schema::hasColumn('student_profiles', 'passport_type')) {
                $table->string('passport_type')->nullable()->after('passport_number');
            }
            if (! Schema::hasColumn('student_profiles', 'passport_issue_date')) {
                $table->date('passport_issue_date')->nullable()->after('passport_type');
            }
            if (! Schema::hasColumn('student_profiles', 'passport_issuing_country')) {
                $table->string('passport_issuing_country')->nullable()->after('passport_expiry_date');
            }
            if (! Schema::hasColumn('student_profiles', 'passport_place_of_issue')) {
                $table->string('passport_place_of_issue')->nullable()->after('passport_issuing_country');
            }
            if (! Schema::hasColumn('student_profiles', 'alternative_phone')) {
                $table->string('alternative_phone')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('student_profiles', 'preferred_messenger')) {
                $table->string('preferred_messenger')->nullable()->after('alternative_phone');
            }
            if (! Schema::hasColumn('student_profiles', 'telegram_username')) {
                $table->string('telegram_username')->nullable()->after('preferred_messenger');
            }
        });

        Schema::table('applications', function (Blueprint $table) {
            if (! Schema::hasColumn('applications', 'application_number')) {
                $table->string('application_number')->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('applications', 'student_type')) {
                $table->string('student_type')->nullable()->after('degree_level');
            }
            if (! Schema::hasColumn('applications', 'intended_intake')) {
                $table->string('intended_intake')->nullable()->after('study_mode');
            }
            if (! Schema::hasColumn('applications', 'terms_agreed_at')) {
                $table->timestamp('terms_agreed_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('applications', 'information_confirmed_at')) {
                $table->timestamp('information_confirmed_at')->nullable()->after('terms_agreed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications', 'application_number')) {
                $table->dropUnique('applications_application_number_unique');
            }

            foreach ([
                'application_number',
                'student_type',
                'intended_intake',
                'terms_agreed_at',
                'information_confirmed_at',
            ] as $column) {
                if (Schema::hasColumn('applications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('student_profiles', function (Blueprint $table) {
            foreach ([
                'full_name_english',
                'country_of_birth',
                'place_of_birth',
                'passport_type',
                'passport_issue_date',
                'passport_issuing_country',
                'passport_place_of_issue',
                'alternative_phone',
                'preferred_messenger',
                'telegram_username',
            ] as $column) {
                if (Schema::hasColumn('student_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

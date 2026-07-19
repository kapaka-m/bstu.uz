<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('student_profiles', 'passport_expiry_date')) {
                $table->date('passport_expiry_date')->nullable()->after('passport_number');
            }
        });

        Schema::table('applications', function (Blueprint $table) {
            if (! Schema::hasColumn('applications', 'faculty_id')) {
                $table->foreignId('faculty_id')->nullable()->after('program_id')->constrained('faculties')->nullOnDelete();
            }
            if (! Schema::hasColumn('applications', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('faculty_id')->constrained('departments')->nullOnDelete();
            }
            if (! Schema::hasColumn('applications', 'degree_level')) {
                $table->string('degree_level')->nullable()->after('department_id');
            }
            if (! Schema::hasColumn('applications', 'language_of_study')) {
                $table->string('language_of_study')->nullable()->after('degree_level');
            }
            if (! Schema::hasColumn('applications', 'study_mode')) {
                $table->string('study_mode')->nullable()->after('language_of_study');
            }
        });

        Schema::table('application_documents', function (Blueprint $table) {
            if (! Schema::hasColumn('application_documents', 'document_type')) {
                $table->string('document_type')->nullable()->after('document_name');
            }
            if (! Schema::hasColumn('application_documents', 'original_name')) {
                $table->string('original_name')->nullable()->after('file_path');
            }
            if (! Schema::hasColumn('application_documents', 'mime_type')) {
                $table->string('mime_type')->nullable()->after('original_name');
            }
            if (! Schema::hasColumn('application_documents', 'size')) {
                $table->unsignedBigInteger('size')->nullable()->after('mime_type');
            }
            if (! Schema::hasColumn('application_documents', 'status')) {
                $table->string('status')->default('pending')->after('size');
            }
            if (! Schema::hasColumn('application_documents', 'note')) {
                $table->text('note')->nullable()->after('status');
            }
        });

        Schema::table('application_status_histories', function (Blueprint $table) {
            if (! Schema::hasColumn('application_status_histories', 'old_status')) {
                $table->string('old_status')->nullable()->after('application_id');
            }
            if (! Schema::hasColumn('application_status_histories', 'new_status')) {
                $table->string('new_status')->nullable()->after('old_status');
            }
            if (! Schema::hasColumn('application_status_histories', 'note')) {
                $table->text('note')->nullable()->after('comment');
            }
        });
    }

    public function down(): void
    {
        Schema::table('application_status_histories', function (Blueprint $table) {
            foreach (['old_status', 'new_status', 'note'] as $column) {
                if (Schema::hasColumn('application_status_histories', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('application_documents', function (Blueprint $table) {
            foreach (['document_type', 'original_name', 'mime_type', 'size', 'status', 'note'] as $column) {
                if (Schema::hasColumn('application_documents', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('applications', function (Blueprint $table) {
            if (Schema::hasColumn('applications', 'department_id')) {
                $table->dropConstrainedForeignId('department_id');
            }
            if (Schema::hasColumn('applications', 'faculty_id')) {
                $table->dropConstrainedForeignId('faculty_id');
            }
            foreach (['degree_level', 'language_of_study', 'study_mode'] as $column) {
                if (Schema::hasColumn('applications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('student_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('student_profiles', 'passport_expiry_date')) {
                $table->dropColumn('passport_expiry_date');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            foreach ([
                'documents_status' => 'NOT_STARTED',
                'equivalency_status' => null,
                'application_fee_status' => 'NOT_REQUIRED',
                'final_review_status' => 'NOT_STARTED',
                'admission_status' => 'NOT_ELIGIBLE',
            ] as $column => $default) {
                if (! Schema::hasColumn('applications', $column)) {
                    $field = $table->string($column)->nullable();
                    if ($default !== null) {
                        $field->default($default);
                    }
                }
            }
            if (! Schema::hasColumn('applications', 'current_step')) {
                $table->string('current_step')->nullable();
            }
            if (! Schema::hasColumn('applications', 'next_action')) {
                $table->text('next_action')->nullable();
            }
            if (! Schema::hasColumn('applications', 'final_reviewed_by')) {
                $table->foreignId('final_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('applications', 'final_reviewed_at')) {
                $table->timestamp('final_reviewed_at')->nullable();
            }
            if (! Schema::hasColumn('applications', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
            if (! Schema::hasColumn('applications', 'correction_reason')) {
                $table->text('correction_reason')->nullable();
            }
        });

        Schema::table('application_documents', function (Blueprint $table) {
            if (! Schema::hasColumn('application_documents', 'student_profile_id')) {
                $table->foreignId('student_profile_id')->nullable()->after('application_id')->constrained('student_profiles')->nullOnDelete();
            }
            if (! Schema::hasColumn('application_documents', 'storage_disk')) {
                $table->string('storage_disk')->default('local')->after('file_path');
            }
            if (! Schema::hasColumn('application_documents', 'stored_filename')) {
                $table->string('stored_filename')->nullable()->after('storage_disk');
            }
            if (! Schema::hasColumn('application_documents', 'current_version')) {
                $table->unsignedInteger('current_version')->default(1)->after('size');
            }
            if (! Schema::hasColumn('application_documents', 'review_status')) {
                $table->string('review_status')->default('UPLOADED')->after('status');
            }
            if (! Schema::hasColumn('application_documents', 'student_notes')) {
                $table->text('student_notes')->nullable()->after('note');
            }
            if (! Schema::hasColumn('application_documents', 'reviewer_id')) {
                $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('application_documents', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable();
            }
            if (! Schema::hasColumn('application_documents', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
            if (! Schema::hasColumn('application_documents', 'internal_admin_notes')) {
                $table->text('internal_admin_notes')->nullable();
            }
            if (! Schema::hasColumn('application_documents', 'previous_document_id')) {
                $table->foreignId('previous_document_id')->nullable()->constrained('application_documents')->nullOnDelete();
            }
            if (! Schema::hasColumn('application_documents', 'is_required')) {
                $table->boolean('is_required')->default(true);
            }
        });

        Schema::create('document_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->nullable()->constrained('applications')->cascadeOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('degree_level')->nullable();
            $table->string('student_type')->nullable();
            $table->string('document_type');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->date('deadline')->nullable();
            $table->text('request_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('application_equivalencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained('applications')->cascadeOnDelete();
            $table->string('status')->default('WAITING_DOCUMENTS');
            $table->string('previous_university')->nullable();
            $table->string('previous_country')->nullable();
            $table->string('previous_program')->nullable();
            $table->string('previous_study_language')->nullable();
            $table->string('previous_education_type')->nullable();
            $table->unsignedInteger('completed_years')->nullable();
            $table->unsignedInteger('completed_semesters')->nullable();
            $table->decimal('completed_credits', 8, 2)->nullable();
            $table->decimal('accepted_credits', 8, 2)->nullable();
            $table->decimal('rejected_credits', 8, 2)->nullable();
            $table->string('proposed_entry_year')->nullable();
            $table->string('proposed_entry_semester')->nullable();
            $table->string('estimated_remaining_duration')->nullable();
            $table->text('general_academic_notes')->nullable();
            $table->text('student_review_reason')->nullable();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('result_issued_at')->nullable();
            $table->timestamp('student_responded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('equivalency_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_equivalency_id')->constrained('application_equivalencies')->cascadeOnDelete();
            $table->string('previous_course_name');
            $table->string('previous_course_code')->nullable();
            $table->decimal('previous_credits', 8, 2)->nullable();
            $table->string('matched_university_course')->nullable();
            $table->string('matched_course_code')->nullable();
            $table->decimal('accepted_credits', 8, 2)->nullable();
            $table->string('course_status')->default('MUST_BE_STUDIED');
            $table->string('required_action')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('application_fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('payment_number')->unique();
            $table->decimal('amount', 10, 2)->default(50);
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('NOT_PAID');
            $table->string('receipt_path')->nullable();
            $table->string('receipt_original_name')->nullable();
            $table->string('receipt_mime_type')->nullable();
            $table->unsignedBigInteger('receipt_size')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('internal_admin_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained('applications')->cascadeOnDelete();
            $table->foreignId('student_profile_id')->constrained('student_profiles')->cascadeOnDelete();
            $table->foreignId('faculty_id')->nullable()->constrained('faculties')->nullOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->string('admission_number')->unique();
            $table->date('issue_date');
            $table->string('status')->default('ISSUED');
            $table->string('student_type')->nullable();
            $table->string('education_type')->nullable();
            $table->string('study_language')->nullable();
            $table->string('estimated_study_duration')->nullable();
            $table->string('document_path')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
        });

        Schema::table('notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('notifications', 'type')) {
                $table->string('type')->nullable()->after('message');
            }
            if (! Schema::hasColumn('notifications', 'related_application_id')) {
                $table->foreignId('related_application_id')->nullable()->constrained('applications')->nullOnDelete();
            }
            if (! Schema::hasColumn('notifications', 'related_entity_type')) {
                $table->string('related_entity_type')->nullable();
            }
            if (! Schema::hasColumn('notifications', 'related_entity_id')) {
                $table->unsignedBigInteger('related_entity_id')->nullable();
            }
            if (! Schema::hasColumn('notifications', 'read_at')) {
                $table->timestamp('read_at')->nullable();
            }
            if (! Schema::hasColumn('notifications', 'action_url')) {
                $table->string('action_url')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admissions');
        Schema::dropIfExists('application_fee_payments');
        Schema::dropIfExists('equivalency_courses');
        Schema::dropIfExists('application_equivalencies');
        Schema::dropIfExists('document_requirements');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            if (! Schema::hasColumn('contracts', 'document_path')) {
                $table->string('document_path')->nullable()->after('status');
            }
            if (! Schema::hasColumn('contracts', 'issued_by')) {
                $table->foreignId('issued_by')->nullable()->after('document_path')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('contracts', 'issued_at')) {
                $table->timestamp('issued_at')->nullable()->after('issued_by');
            }
        });

        Schema::create('prikazes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained('applications')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained('enrollments')->nullOnDelete();
            $table->foreignId('student_profile_id')->constrained('student_profiles')->cascadeOnDelete();
            $table->foreignId('program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->string('prikaz_number')->unique();
            $table->date('issue_date')->nullable();
            $table->string('academic_year')->nullable();
            $table->string('status')->default('ISSUED');
            $table->string('document_path')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
        });

        Schema::create('student_visa_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained('applications')->cascadeOnDelete();
            $table->foreignId('student_profile_id')->constrained('student_profiles')->cascadeOnDelete();
            $table->string('telex_number')->nullable();
            $table->string('telex_status')->default('NOT_STARTED');
            $table->string('visa_status')->default('NOT_STARTED');
            $table->text('visa_notes')->nullable();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('telex_issued_at')->nullable();
            $table->timestamp('visa_updated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('housing_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained('applications')->cascadeOnDelete();
            $table->foreignId('student_profile_id')->constrained('student_profiles')->cascadeOnDelete();
            $table->boolean('requested')->default(false);
            $table->string('status')->default('NOT_REQUESTED');
            $table->string('preferred_room_type')->nullable();
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('residence_permit_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->unique()->constrained('applications')->cascadeOnDelete();
            $table->foreignId('student_profile_id')->constrained('student_profiles')->cascadeOnDelete();
            $table->string('status')->default('NOT_STARTED');
            $table->text('notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('service_fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('payment_number')->unique();
            $table->decimal('amount', 10, 2)->default(300);
            $table->string('currency', 10)->default('USD');
            $table->string('status')->default('UPLOADED');
            $table->string('receipt_path')->nullable();
            $table->string('receipt_original_name')->nullable();
            $table->string('receipt_mime_type')->nullable();
            $table->unsignedBigInteger('receipt_size')->nullable();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_fee_payments');
        Schema::dropIfExists('residence_permit_processes');
        Schema::dropIfExists('housing_requests');
        Schema::dropIfExists('student_visa_processes');
        Schema::dropIfExists('prikazes');

        Schema::table('contracts', function (Blueprint $table) {
            foreach (['issued_at', 'issued_by', 'document_path'] as $column) {
                if (Schema::hasColumn('contracts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

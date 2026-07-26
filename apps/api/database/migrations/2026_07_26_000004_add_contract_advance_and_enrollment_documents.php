<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            if (! Schema::hasColumn('contracts', 'currency')) {
                $table->string('currency', 10)->default('USD')->after('amount');
            }
            if (! Schema::hasColumn('contracts', 'advance_percentage')) {
                $table->unsignedTinyInteger('advance_percentage')->default(30)->after('currency');
            }
            if (! Schema::hasColumn('contracts', 'advance_amount')) {
                $table->decimal('advance_amount', 10, 2)->nullable()->after('advance_percentage');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'payment_type')) {
                $table->string('payment_type')->default('contract_advance')->after('payment_number');
            }
            if (! Schema::hasColumn('payments', 'currency')) {
                $table->string('currency', 10)->default('USD')->after('amount');
            }
            if (! Schema::hasColumn('payments', 'receipt_path')) {
                $table->string('receipt_path')->nullable()->after('status');
            }
            if (! Schema::hasColumn('payments', 'receipt_original_name')) {
                $table->string('receipt_original_name')->nullable()->after('receipt_path');
            }
            if (! Schema::hasColumn('payments', 'receipt_mime_type')) {
                $table->string('receipt_mime_type')->nullable()->after('receipt_original_name');
            }
            if (! Schema::hasColumn('payments', 'receipt_size')) {
                $table->unsignedBigInteger('receipt_size')->nullable()->after('receipt_mime_type');
            }
            if (! Schema::hasColumn('payments', 'reviewer_id')) {
                $table->foreignId('reviewer_id')->nullable()->after('receipt_size')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('payments', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewer_id');
            }
            if (! Schema::hasColumn('payments', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('reviewed_at');
            }
        });

        Schema::table('enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('enrollments', 'application_id')) {
                $table->foreignId('application_id')->nullable()->after('id')->unique()->constrained('applications')->cascadeOnDelete();
            }
            if (! Schema::hasColumn('enrollments', 'admission_id')) {
                $table->foreignId('admission_id')->nullable()->after('application_id')->constrained('admissions')->nullOnDelete();
            }
            if (! Schema::hasColumn('enrollments', 'issue_date')) {
                $table->date('issue_date')->nullable()->after('academic_year');
            }
            if (! Schema::hasColumn('enrollments', 'document_path')) {
                $table->string('document_path')->nullable()->after('status');
            }
            if (! Schema::hasColumn('enrollments', 'issued_by')) {
                $table->foreignId('issued_by')->nullable()->after('document_path')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('enrollments', 'issued_at')) {
                $table->timestamp('issued_at')->nullable()->after('issued_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            foreach (['issued_at', 'issued_by', 'document_path', 'issue_date', 'admission_id', 'application_id'] as $column) {
                if (Schema::hasColumn('enrollments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            foreach (['rejection_reason', 'reviewed_at', 'reviewer_id', 'receipt_size', 'receipt_mime_type', 'receipt_original_name', 'receipt_path', 'currency', 'payment_type'] as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('contracts', function (Blueprint $table) {
            foreach (['advance_amount', 'advance_percentage', 'currency'] as $column) {
                if (Schema::hasColumn('contracts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

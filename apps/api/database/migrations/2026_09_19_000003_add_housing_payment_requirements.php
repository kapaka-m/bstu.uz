<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('housing_requests', function (Blueprint $table) {
            $table->string('pinfl', 14)->nullable();
            $table->timestamp('pinfl_verified_at')->nullable();
            $table->foreignId('pinfl_verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_month')->nullable();
        });
        Schema::create('housing_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_request_id')->constrained()->restrictOnDelete();
            $table->date('month');
            $table->unsignedInteger('version')->default(1);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('status')->default('UPLOADED');
            $table->string('receipt_path');
            $table->string('receipt_original_name');
            $table->string('receipt_mime_type');
            $table->unsignedBigInteger('receipt_size');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->unique(['housing_request_id', 'month', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('housing_payments');
        Schema::table('housing_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pinfl_verified_by');
            $table->dropColumn(['pinfl', 'pinfl_verified_at', 'start_month']);
        });
    }
};

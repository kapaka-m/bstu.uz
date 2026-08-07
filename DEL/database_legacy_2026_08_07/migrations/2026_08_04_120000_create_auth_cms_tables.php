<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('auth_pages')) {
            Schema::create('auth_pages', function (Blueprint $table) {
                $table->id();
                $table->string('page_key')->unique();
                $table->boolean('is_active')->default(true);
                $table->json('settings')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('auth_page_translations')) {
            Schema::create('auth_page_translations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('auth_page_id')->constrained()->cascadeOnDelete();
                $table->string('locale', 10);
                $table->string('title')->nullable();
                $table->text('subtitle')->nullable();
                $table->string('email_label')->nullable();
                $table->string('email_placeholder')->nullable();
                $table->string('password_label')->nullable();
                $table->string('password_placeholder')->nullable();
                $table->string('confirm_password_label')->nullable();
                $table->string('confirm_password_placeholder')->nullable();
                $table->string('submit_label')->nullable();
                $table->string('loading_label')->nullable();
                $table->string('forgot_password_label')->nullable();
                $table->string('secondary_text')->nullable();
                $table->string('secondary_action_label')->nullable();
                $table->string('secondary_action_url')->nullable();
                $table->string('success_title')->nullable();
                $table->text('success_message')->nullable();
                $table->string('back_label')->nullable();
                $table->string('show_password_label')->nullable();
                $table->string('hide_password_label')->nullable();
                $table->string('validation_required_message')->nullable();
                $table->string('validation_mismatch_message')->nullable();
                $table->string('error_message')->nullable();
                $table->string('logo_alt')->nullable();
                $table->timestamps();

                $table->unique(['auth_page_id', 'locale'], 'auth_page_locale_unique');
            });
        }

        if (Schema::hasTable('auth_page_translations') && ! Schema::hasColumn('auth_page_translations', 'forgot_password_label')) {
            Schema::table('auth_page_translations', function (Blueprint $table) {
                $table->string('forgot_password_label')->nullable()->after('loading_label');
            });
        }

        if (! Schema::hasTable('auth_email_templates')) {
            Schema::create('auth_email_templates', function (Blueprint $table) {
                $table->id();
                $table->string('template_key')->unique();
                $table->boolean('is_active')->default(true);
                $table->json('settings')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('auth_email_template_translations')) {
            Schema::create('auth_email_template_translations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('auth_email_template_id')->constrained()->cascadeOnDelete();
                $table->string('locale', 10);
                $table->string('subject')->nullable();
                $table->string('brand_name')->nullable();
                $table->string('greeting')->nullable();
                $table->text('intro')->nullable();
                $table->string('action_label')->nullable();
                $table->string('expiry_notice')->nullable();
                $table->text('no_action_notice')->nullable();
                $table->string('salutation')->nullable();
                $table->string('signature')->nullable();
                $table->text('subcopy')->nullable();
                $table->string('footer')->nullable();
                $table->timestamps();

                $table->unique(['auth_email_template_id', 'locale'], 'auth_email_template_locale_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_email_template_translations');
        Schema::dropIfExists('auth_email_templates');
        Schema::dropIfExists('auth_page_translations');
        Schema::dropIfExists('auth_pages');
    }
};

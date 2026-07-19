<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_footers', function (Blueprint $table) {
            if (! Schema::hasColumn('web_footers', 'admissions_apply_url')) {
                $table->string('admissions_apply_url')->nullable()->after('social_links');
            }
        });

        Schema::table('web_footer_translations', function (Blueprint $table) {
            $columns = [
                'admissions_badge' => fn () => $table->string('admissions_badge')->nullable()->after('description'),
                'admissions_heading' => fn () => $table->string('admissions_heading')->nullable()->after('admissions_badge'),
                'admissions_description' => fn () => $table->text('admissions_description')->nullable()->after('admissions_heading'),
                'admissions_button_label' => fn () => $table->string('admissions_button_label')->nullable()->after('admissions_description'),
                'newsletter_title' => fn () => $table->string('newsletter_title')->nullable()->after('admissions_button_label'),
                'newsletter_description' => fn () => $table->text('newsletter_description')->nullable()->after('newsletter_title'),
                'newsletter_placeholder' => fn () => $table->string('newsletter_placeholder')->nullable()->after('newsletter_description'),
                'newsletter_success_message' => fn () => $table->string('newsletter_success_message')->nullable()->after('newsletter_placeholder'),
            ];

            foreach ($columns as $column => $definition) {
                if (! Schema::hasColumn('web_footer_translations', $column)) {
                    $definition();
                }
            }
        });

        Schema::create('newsletter_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('locale', 5)->default('en');
            $table->string('status')->default('active');
            $table->timestamp('subscribed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_subscriptions');

        Schema::table('web_footer_translations', function (Blueprint $table) {
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
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('web_footers', function (Blueprint $table) {
            if (Schema::hasColumn('web_footers', 'admissions_apply_url')) {
                $table->dropColumn('admissions_apply_url');
            }
        });
    }
};

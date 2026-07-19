<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add composite indexes for high-read public CMS and student/apanel workflows.
     */
    public function up(): void
    {
        Schema::table('faculties', function (Blueprint $table) {
            $table->index(['is_active', 'sort_order'], 'faculties_active_sort_idx');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->index(['faculty_id', 'is_active', 'sort_order'], 'departments_faculty_active_sort_idx');
            $table->index(['is_active', 'sort_order'], 'departments_active_sort_idx');
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->index(['faculty_id', 'is_active', 'sort_order'], 'programs_faculty_active_sort_idx');
            $table->index(['department_id', 'is_active', 'sort_order'], 'programs_department_active_sort_idx');
            $table->index(['official_code', 'track'], 'programs_official_track_idx');
        });

        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->index(['faculty_id', 'is_active', 'sort_order'], 'staff_faculty_active_sort_idx');
            $table->index(['department_id', 'is_active', 'sort_order'], 'staff_department_active_sort_idx');
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->index(['is_published', 'sort_order'], 'pages_published_sort_idx');
        });

        Schema::table('page_blocks', function (Blueprint $table) {
            $table->index(['page_id', 'is_active', 'sort_order'], 'page_blocks_page_active_sort_idx');
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->index(['location', 'is_active'], 'menus_location_active_idx');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->index(['menu_id', 'parent_id', 'is_active', 'sort_order'], 'menu_items_tree_active_sort_idx');
        });

        Schema::table('news', function (Blueprint $table) {
            $table->index(['is_published', 'published_at'], 'news_published_date_idx');
            $table->index(['category', 'is_published', 'published_at'], 'news_category_published_date_idx');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->index(['is_published', 'priority', 'created_at'], 'announcements_public_order_idx');
            $table->index(['is_published', 'ends_at'], 'announcements_active_idx');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->index(['is_active', 'sort_order'], 'services_active_sort_idx');
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->index(['is_active', 'sort_order'], 'videos_active_sort_idx');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->index(['is_public', 'group'], 'settings_public_group_idx');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->index(['is_public', 'type'], 'media_public_type_idx');
        });

        Schema::table('green_campus_stats', function (Blueprint $table) {
            $table->index('sort_order', 'green_stats_sort_idx');
        });

        Schema::table('green_campus_articles', function (Blueprint $table) {
            $table->index(['created_at'], 'green_articles_created_idx');
        });

        Schema::table('student_profiles', function (Blueprint $table) {
            $table->index(['user_id', 'passport_number'], 'student_profiles_user_passport_idx');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->index(['student_profile_id', 'status', 'created_at'], 'applications_student_status_date_idx');
            $table->index(['program_id', 'status'], 'applications_program_status_idx');
            $table->index(['faculty_id', 'status'], 'applications_faculty_status_idx');
            $table->index(['department_id', 'status'], 'applications_department_status_idx');
        });

        Schema::table('application_documents', function (Blueprint $table) {
            $table->index(['application_id', 'document_type', 'status'], 'app_docs_application_type_status_idx');
        });

        Schema::table('application_status_histories', function (Blueprint $table) {
            $table->index(['application_id', 'created_at'], 'app_status_application_date_idx');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->index(['application_id', 'status'], 'contracts_application_status_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['contract_id', 'status', 'payment_date'], 'payments_contract_status_date_idx');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'is_read', 'created_at'], 'notifications_user_read_date_idx');
        });

        Schema::table('document_requests', function (Blueprint $table) {
            $table->index(['student_profile_id', 'status'], 'doc_requests_student_status_idx');
        });

        Schema::table('inquiries', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'inquiries_status_date_idx');
        });

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->index(['user_id', 'status', 'created_at'], 'support_tickets_user_status_date_idx');
        });

        Schema::table('support_ticket_messages', function (Blueprint $table) {
            $table->index(['support_ticket_id', 'created_at'], 'support_messages_ticket_date_idx');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index(['user_id', 'created_at'], 'audit_logs_user_date_idx');
            $table->index(['model_type', 'model_id'], 'audit_logs_model_idx');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('audit_logs_model_idx');
            $table->dropIndex('audit_logs_user_date_idx');
        });
        Schema::table('support_ticket_messages', function (Blueprint $table) {
            $table->dropIndex('support_messages_ticket_date_idx');
        });
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropIndex('support_tickets_user_status_date_idx');
        });
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropIndex('inquiries_status_date_idx');
        });
        Schema::table('document_requests', function (Blueprint $table) {
            $table->dropIndex('doc_requests_student_status_idx');
        });
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notifications_user_read_date_idx');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_contract_status_date_idx');
        });
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropIndex('contracts_application_status_idx');
        });
        Schema::table('application_status_histories', function (Blueprint $table) {
            $table->dropIndex('app_status_application_date_idx');
        });
        Schema::table('application_documents', function (Blueprint $table) {
            $table->dropIndex('app_docs_application_type_status_idx');
        });
        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex('applications_department_status_idx');
            $table->dropIndex('applications_faculty_status_idx');
            $table->dropIndex('applications_program_status_idx');
            $table->dropIndex('applications_student_status_date_idx');
        });
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropIndex('student_profiles_user_passport_idx');
        });
        Schema::table('green_campus_articles', function (Blueprint $table) {
            $table->dropIndex('green_articles_created_idx');
        });
        Schema::table('green_campus_stats', function (Blueprint $table) {
            $table->dropIndex('green_stats_sort_idx');
        });
        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex('media_public_type_idx');
        });
        Schema::table('settings', function (Blueprint $table) {
            $table->dropIndex('settings_public_group_idx');
        });
        Schema::table('videos', function (Blueprint $table) {
            $table->dropIndex('videos_active_sort_idx');
        });
        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex('services_active_sort_idx');
        });
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropIndex('announcements_active_idx');
            $table->dropIndex('announcements_public_order_idx');
        });
        Schema::table('news', function (Blueprint $table) {
            $table->dropIndex('news_category_published_date_idx');
            $table->dropIndex('news_published_date_idx');
        });
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropIndex('menu_items_tree_active_sort_idx');
        });
        Schema::table('menus', function (Blueprint $table) {
            $table->dropIndex('menus_location_active_idx');
        });
        Schema::table('page_blocks', function (Blueprint $table) {
            $table->dropIndex('page_blocks_page_active_sort_idx');
        });
        Schema::table('pages', function (Blueprint $table) {
            $table->dropIndex('pages_published_sort_idx');
        });
        Schema::table('staff_profiles', function (Blueprint $table) {
            $table->dropIndex('staff_department_active_sort_idx');
            $table->dropIndex('staff_faculty_active_sort_idx');
        });
        Schema::table('programs', function (Blueprint $table) {
            $table->dropIndex('programs_official_track_idx');
            $table->dropIndex('programs_department_active_sort_idx');
            $table->dropIndex('programs_faculty_active_sort_idx');
        });
        Schema::table('departments', function (Blueprint $table) {
            $table->dropIndex('departments_active_sort_idx');
            $table->dropIndex('departments_faculty_active_sort_idx');
        });
        Schema::table('faculties', function (Blueprint $table) {
            $table->dropIndex('faculties_active_sort_idx');
        });
    }
};

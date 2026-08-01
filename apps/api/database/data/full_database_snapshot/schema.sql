--
-- Database: `bstu_international`
--

-- --------------------------------------------------------

--
-- Table structure for table `about_pages`
--

CREATE TABLE `about_pages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL DEFAULT 'main',
  `hero_contact_url` varchar(255) DEFAULT NULL,
  `hero_campus_url` varchar(255) DEFAULT NULL,
  `identity_image` varchar(255) DEFAULT NULL,
  `rector_profile_slug` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `about_page_content_entries`
--

CREATE TABLE `about_page_content_entries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `about_page_id` bigint(20) UNSIGNED NOT NULL,
  `path` varchar(255) NOT NULL,
  `value_type` varchar(30) NOT NULL DEFAULT 'text',
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `about_page_content_entry_translations`
--

CREATE TABLE `about_page_content_entry_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `about_page_content_entry_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(5) NOT NULL,
  `value` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `about_page_translations`
--

CREATE TABLE `about_page_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `about_page_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(5) NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`content`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `administration_profiles`
--

CREATE TABLE `administration_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `telegram_url` varchar(255) DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_rector` tinyint(1) NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `administration_profile_translations`
--

CREATE TABLE `administration_profile_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `administration_profile_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(5) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `degree` varchar(255) DEFAULT NULL,
  `office_hours` varchar(255) DEFAULT NULL,
  `about` text DEFAULT NULL,
  `details` longtext DEFAULT NULL,
  `achievements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`achievements`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `administration_settings`
--

CREATE TABLE `administration_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL DEFAULT 'main',
  `home_limit` int(10) UNSIGNED NOT NULL DEFAULT 6,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `administration_setting_translations`
--

CREATE TABLE `administration_setting_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `administration_setting_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(5) NOT NULL,
  `home_tag` varchar(255) DEFAULT NULL,
  `home_title` varchar(255) DEFAULT NULL,
  `reception_label` varchar(255) DEFAULT NULL,
  `phone_label` varchar(255) DEFAULT NULL,
  `email_label` varchar(255) DEFAULT NULL,
  `telegram_label` varchar(255) DEFAULT NULL,
  `rector_bot_label` varchar(255) DEFAULT NULL,
  `structure_title` varchar(255) DEFAULT NULL,
  `profile_category_label` varchar(255) DEFAULT NULL,
  `email_address_label` varchar(255) DEFAULT NULL,
  `phone_number_label` varchar(255) DEFAULT NULL,
  `office_hours_label` varchar(255) DEFAULT NULL,
  `academic_rank_label` varchar(255) DEFAULT NULL,
  `biography_label` varchar(255) DEFAULT NULL,
  `duties_label` varchar(255) DEFAULT NULL,
  `achievements_label` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admissions`
--

CREATE TABLE `admissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `student_profile_id` bigint(20) UNSIGNED NOT NULL,
  `faculty_id` bigint(20) UNSIGNED DEFAULT NULL,
  `program_id` bigint(20) UNSIGNED DEFAULT NULL,
  `admission_number` varchar(255) NOT NULL,
  `issue_date` date NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'ISSUED',
  `student_type` varchar(255) DEFAULT NULL,
  `education_type` varchar(255) DEFAULT NULL,
  `study_language` varchar(255) DEFAULT NULL,
  `estimated_study_duration` varchar(255) DEFAULT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `issued_by` bigint(20) UNSIGNED DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `priority` varchar(255) NOT NULL DEFAULT 'normal',
  `image` varchar(255) DEFAULT NULL,
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `views_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcement_settings`
--

CREATE TABLE `announcement_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL DEFAULT 'main',
  `home_limit` tinyint(3) UNSIGNED NOT NULL DEFAULT 4,
  `recent_limit` tinyint(3) UNSIGNED NOT NULL DEFAULT 5,
  `important_limit` tinyint(3) UNSIGNED NOT NULL DEFAULT 3,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcement_setting_translations`
--

CREATE TABLE `announcement_setting_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `announcement_setting_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(5) NOT NULL,
  `home_tag` varchar(255) DEFAULT NULL,
  `home_title` varchar(255) DEFAULT NULL,
  `view_all_label` varchar(255) DEFAULT NULL,
  `read_details_label` varchar(255) DEFAULT NULL,
  `search_title` varchar(255) DEFAULT NULL,
  `search_placeholder` varchar(255) DEFAULT NULL,
  `categories_title` varchar(255) DEFAULT NULL,
  `recent_title` varchar(255) DEFAULT NULL,
  `all_label` varchar(255) DEFAULT NULL,
  `views_label` varchar(255) DEFAULT NULL,
  `important_label` varchar(255) DEFAULT NULL,
  `loading_label` varchar(255) DEFAULT NULL,
  `no_results_label` varchar(255) DEFAULT NULL,
  `clear_filters_label` varchar(255) DEFAULT NULL,
  `share_label` varchar(255) DEFAULT NULL,
  `copy_link_label` varchar(255) DEFAULT NULL,
  `copied_label` varchar(255) DEFAULT NULL,
  `published_by_label` varchar(255) DEFAULT NULL,
  `publisher_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `announcement_translations`
--

CREATE TABLE `announcement_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `announcement_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(255) NOT NULL,
  `category_label` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_number` varchar(255) DEFAULT NULL,
  `student_profile_id` bigint(20) UNSIGNED NOT NULL,
  `program_id` bigint(20) UNSIGNED NOT NULL,
  `faculty_id` bigint(20) UNSIGNED DEFAULT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `degree_level` varchar(255) DEFAULT NULL,
  `student_type` varchar(255) DEFAULT NULL,
  `language_of_study` varchar(255) DEFAULT NULL,
  `study_mode` varchar(255) DEFAULT NULL,
  `intended_intake` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `terms_agreed_at` timestamp NULL DEFAULT NULL,
  `information_confirmed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `documents_status` varchar(255) DEFAULT 'NOT_STARTED',
  `equivalency_status` varchar(255) DEFAULT NULL,
  `application_fee_status` varchar(255) DEFAULT 'NOT_REQUIRED',
  `final_review_status` varchar(255) DEFAULT 'NOT_STARTED',
  `admission_status` varchar(255) DEFAULT 'NOT_ELIGIBLE',
  `current_step` varchar(255) DEFAULT NULL,
  `next_action` text DEFAULT NULL,
  `final_reviewed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `final_reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `correction_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `application_countries`
--

CREATE TABLE `application_countries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(3) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `application_documents`
--

CREATE TABLE `application_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `student_profile_id` bigint(20) UNSIGNED DEFAULT NULL,
  `document_name` varchar(255) NOT NULL,
  `document_type` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `storage_disk` varchar(255) NOT NULL DEFAULT 'local',
  `stored_filename` varchar(255) DEFAULT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `size` bigint(20) UNSIGNED DEFAULT NULL,
  `current_version` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `review_status` varchar(255) NOT NULL DEFAULT 'UPLOADED',
  `note` text DEFAULT NULL,
  `student_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `reviewer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `internal_admin_notes` text DEFAULT NULL,
  `previous_document_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `application_equivalencies`
--

CREATE TABLE `application_equivalencies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'WAITING_DOCUMENTS',
  `previous_university` varchar(255) DEFAULT NULL,
  `previous_country` varchar(255) DEFAULT NULL,
  `previous_program` varchar(255) DEFAULT NULL,
  `previous_study_language` varchar(255) DEFAULT NULL,
  `previous_education_type` varchar(255) DEFAULT NULL,
  `completed_years` int(10) UNSIGNED DEFAULT NULL,
  `completed_semesters` int(10) UNSIGNED DEFAULT NULL,
  `completed_credits` decimal(8,2) DEFAULT NULL,
  `accepted_credits` decimal(8,2) DEFAULT NULL,
  `rejected_credits` decimal(8,2) DEFAULT NULL,
  `proposed_entry_year` varchar(255) DEFAULT NULL,
  `proposed_entry_semester` varchar(255) DEFAULT NULL,
  `estimated_remaining_duration` varchar(255) DEFAULT NULL,
  `general_academic_notes` text DEFAULT NULL,
  `student_review_reason` text DEFAULT NULL,
  `reviewer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `result_issued_at` timestamp NULL DEFAULT NULL,
  `student_responded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `application_fee_payments`
--

CREATE TABLE `application_fee_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `payment_number` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 50.00,
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  `status` varchar(255) NOT NULL DEFAULT 'NOT_PAID',
  `receipt_path` varchar(255) DEFAULT NULL,
  `receipt_original_name` varchar(255) DEFAULT NULL,
  `receipt_mime_type` varchar(255) DEFAULT NULL,
  `receipt_size` bigint(20) UNSIGNED DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `reviewer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `internal_admin_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `application_nationalities`
--

CREATE TABLE `application_nationalities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `country_name` varchar(255) DEFAULT NULL,
  `code` varchar(3) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `application_status_histories`
--

CREATE TABLE `application_status_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `old_status` varchar(255) DEFAULT NULL,
  `new_status` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `comment` text DEFAULT NULL,
  `note` text DEFAULT NULL,
  `changed_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `model_type` varchar(255) DEFAULT NULL,
  `model_id` bigint(20) UNSIGNED DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `author_image` varchar(255) DEFAULT NULL,
  `category` varchar(255) NOT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `views_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `comments_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_comments`
--

CREATE TABLE `blog_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `blog_id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `author_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_settings`
--

CREATE TABLE `blog_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `home_limit` tinyint(3) UNSIGNED NOT NULL DEFAULT 3,
  `recent_limit` tinyint(3) UNSIGNED NOT NULL DEFAULT 5,
  `home_icon` varchar(255) NOT NULL DEFAULT 'book-open',
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_setting_translations`
--

CREATE TABLE `blog_setting_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `blog_setting_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(5) NOT NULL,
  `home_tag` varchar(255) DEFAULT NULL,
  `home_title` varchar(255) DEFAULT NULL,
  `view_all_label` varchar(255) DEFAULT NULL,
  `read_more_label` varchar(255) DEFAULT NULL,
  `search_title` varchar(255) DEFAULT NULL,
  `search_placeholder` varchar(255) DEFAULT NULL,
  `categories_title` varchar(255) DEFAULT NULL,
  `recent_title` varchar(255) DEFAULT NULL,
  `tags_title` varchar(255) DEFAULT NULL,
  `all_blog_label` varchar(255) DEFAULT NULL,
  `loading_label` varchar(255) DEFAULT NULL,
  `no_results_label` varchar(255) DEFAULT NULL,
  `clear_filters_label` varchar(255) DEFAULT NULL,
  `back_to_blog_label` varchar(255) DEFAULT NULL,
  `comments_label` varchar(255) DEFAULT NULL,
  `reply_label` varchar(255) DEFAULT NULL,
  `form_title` varchar(255) DEFAULT NULL,
  `form_name_label` varchar(255) DEFAULT NULL,
  `form_email_label` varchar(255) DEFAULT NULL,
  `form_comment_label` varchar(255) DEFAULT NULL,
  `form_submit_label` varchar(255) DEFAULT NULL,
  `signed_in_as_label` varchar(255) DEFAULT NULL,
  `comment_login_action` varchar(255) DEFAULT NULL,
  `comment_login_text` varchar(255) DEFAULT NULL,
  `comment_login_title` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_translations`
--

CREATE TABLE `blog_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `blog_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(5) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `category_label` varchar(255) DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `commentable_type` varchar(255) NOT NULL,
  `commentable_id` bigint(20) UNSIGNED NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_pages`
--

CREATE TABLE `contact_pages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL DEFAULT 'main',
  `map_embed_url` text DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_page_translations`
--

CREATE TABLE `contact_page_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `contact_page_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(5) NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`content`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contracts`
--

CREATE TABLE `contracts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `contract_number` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `advance_percentage` tinyint(3) UNSIGNED NOT NULL DEFAULT 30,
  `advance_amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `document_path` varchar(255) DEFAULT NULL,
  `issued_by` bigint(20) UNSIGNED DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `credits` int(11) NOT NULL,
  `semester` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_translations`
--

CREATE TABLE `course_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `faculty_id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(255) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `head_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `reception_time` varchar(255) DEFAULT NULL,
  `source_url` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `department_translations`
--

CREATE TABLE `department_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `short_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `content_sections` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`content_sections`)),
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_requests`
--

CREATE TABLE `document_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_profile_id` bigint(20) UNSIGNED NOT NULL,
  `request_type` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_requirements`
--

CREATE TABLE `document_requirements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED DEFAULT NULL,
  `program_id` bigint(20) UNSIGNED DEFAULT NULL,
  `requested_by` bigint(20) UNSIGNED DEFAULT NULL,
  `degree_level` varchar(255) DEFAULT NULL,
  `student_type` varchar(255) DEFAULT NULL,
  `document_type` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `deadline` date DEFAULT NULL,
  `request_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `education_backgrounds`
--

CREATE TABLE `education_backgrounds` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_profile_id` bigint(20) UNSIGNED NOT NULL,
  `institution_name` varchar(255) NOT NULL,
  `degree_obtained` varchar(255) NOT NULL,
  `gpa` varchar(255) NOT NULL,
  `graduation_year` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED DEFAULT NULL,
  `admission_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_profile_id` bigint(20) UNSIGNED NOT NULL,
  `program_id` bigint(20) UNSIGNED NOT NULL,
  `student_number` varchar(255) NOT NULL,
  `academic_year` varchar(255) NOT NULL,
  `issue_date` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `document_path` varchar(255) DEFAULT NULL,
  `issued_by` bigint(20) UNSIGNED DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equivalency_courses`
--

CREATE TABLE `equivalency_courses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_equivalency_id` bigint(20) UNSIGNED NOT NULL,
  `previous_course_name` varchar(255) NOT NULL,
  `previous_course_code` varchar(255) DEFAULT NULL,
  `previous_credits` decimal(8,2) DEFAULT NULL,
  `matched_university_course` varchar(255) DEFAULT NULL,
  `matched_course_code` varchar(255) DEFAULT NULL,
  `accepted_credits` decimal(8,2) DEFAULT NULL,
  `course_status` varchar(255) NOT NULL DEFAULT 'MUST_BE_STUDIED',
  `required_action` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faculties`
--

CREATE TABLE `faculties` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(255) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faculty_translations`
--

CREATE TABLE `faculty_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `faculty_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `short_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `content_sections` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`content_sections`)),
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` varchar(255) NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `green_campus_articles`
--

CREATE TABLE `green_campus_articles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `gallery` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gallery`)),
  `views` int(11) NOT NULL DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `green_campus_article_translations`
--

CREATE TABLE `green_campus_article_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `green_campus_article_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `green_campus_settings`
--

CREATE TABLE `green_campus_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL DEFAULT 'main',
  `home_limit` int(11) NOT NULL DEFAULT 3,
  `recent_limit` int(11) NOT NULL DEFAULT 4,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `green_campus_setting_translations`
--

CREATE TABLE `green_campus_setting_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `green_campus_setting_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(255) NOT NULL,
  `home_tag` varchar(255) DEFAULT NULL,
  `home_title` varchar(255) DEFAULT NULL,
  `view_all_label` varchar(255) DEFAULT NULL,
  `read_more_label` varchar(255) DEFAULT NULL,
  `search_title` varchar(255) DEFAULT NULL,
  `search_placeholder` varchar(255) DEFAULT NULL,
  `categories_title` varchar(255) DEFAULT NULL,
  `recent_title` varchar(255) DEFAULT NULL,
  `all_label` varchar(255) DEFAULT NULL,
  `no_results_label` varchar(255) DEFAULT NULL,
  `callout_title` varchar(255) DEFAULT NULL,
  `callout_description` text DEFAULT NULL,
  `callout_cta_label` varchar(255) DEFAULT NULL,
  `callout_email` varchar(255) DEFAULT NULL,
  `views_label` varchar(255) DEFAULT NULL,
  `gallery_label` varchar(255) DEFAULT NULL,
  `related_label` varchar(255) DEFAULT NULL,
  `close_viewer_label` varchar(255) DEFAULT NULL,
  `previous_image_label` varchar(255) DEFAULT NULL,
  `next_image_label` varchar(255) DEFAULT NULL,
  `category_labels` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`category_labels`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `green_campus_stats`
--

CREATE TABLE `green_campus_stats` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `green_campus_stat_translations`
--

CREATE TABLE `green_campus_stat_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `green_campus_stat_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guardians`
--

CREATE TABLE `guardians` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `student_profile_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `relation` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `housing_requests`
--

CREATE TABLE `housing_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `student_profile_id` bigint(20) UNSIGNED NOT NULL,
  `requested` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'NOT_REQUESTED',
  `preferred_room_type` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `reviewer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

CREATE TABLE `inquiries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `read_at` timestamp NULL DEFAULT NULL,
  `reply_message` text DEFAULT NULL,
  `replied_at` timestamp NULL DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interactive_service_settings`
--

CREATE TABLE `interactive_service_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL DEFAULT 'main',
  `home_limit` int(11) NOT NULL DEFAULT 4,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interactive_service_setting_translations`
--

CREATE TABLE `interactive_service_setting_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `interactive_service_setting_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(255) NOT NULL,
  `home_tag` varchar(255) DEFAULT NULL,
  `home_title` varchar(255) DEFAULT NULL,
  `view_all_label` varchar(255) DEFAULT NULL,
  `loading_label` varchar(255) DEFAULT NULL,
  `no_results_label` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` smallint(5) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `locales`
--

CREATE TABLE `locales` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `native_name` varchar(255) NOT NULL,
  `direction` enum('ltr','rtl') NOT NULL DEFAULT 'ltr',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

CREATE TABLE `media` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `disk` varchar(255) NOT NULL DEFAULT 'public',
  `path` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `alt_text` text DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `mime_type` varchar(255) NOT NULL,
  `size` bigint(20) UNSIGNED NOT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 1,
  `alt_key` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `menu_id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `route_name` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menu_item_translations`
--

CREATE TABLE `menu_item_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `menu_item_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(255) NOT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `views_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscriptions`
--

CREATE TABLE `newsletter_subscriptions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `locale` varchar(5) NOT NULL DEFAULT 'en',
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `subscribed_at` timestamp NULL DEFAULT NULL,
  `unsubscribed_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news_event_settings`
--

CREATE TABLE `news_event_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `home_limit` tinyint(3) UNSIGNED NOT NULL DEFAULT 4,
  `recent_limit` tinyint(3) UNSIGNED NOT NULL DEFAULT 5,
  `home_icon` varchar(255) NOT NULL DEFAULT 'newspaper',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news_event_setting_translations`
--

CREATE TABLE `news_event_setting_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `news_event_setting_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(5) NOT NULL,
  `home_tag` varchar(255) DEFAULT NULL,
  `home_title` varchar(255) DEFAULT NULL,
  `home_subtitle` text DEFAULT NULL,
  `view_all_label` varchar(255) DEFAULT NULL,
  `read_details_label` varchar(255) DEFAULT NULL,
  `search_title` varchar(255) DEFAULT NULL,
  `search_placeholder` varchar(255) DEFAULT NULL,
  `categories_title` varchar(255) DEFAULT NULL,
  `recent_title` varchar(255) DEFAULT NULL,
  `all_news_label` varchar(255) DEFAULT NULL,
  `news_label` varchar(255) DEFAULT NULL,
  `events_label` varchar(255) DEFAULT NULL,
  `views_label` varchar(255) DEFAULT NULL,
  `loading_label` varchar(255) DEFAULT NULL,
  `no_results_label` varchar(255) DEFAULT NULL,
  `clear_filters_label` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news_translations`
--

CREATE TABLE `news_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `news_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `related_application_id` bigint(20) UNSIGNED DEFAULT NULL,
  `related_entity_type` varchar(255) DEFAULT NULL,
  `related_entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `action_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(255) NOT NULL,
  `template` varchar(255) NOT NULL DEFAULT 'default',
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `page_blocks`
--

CREATE TABLE `page_blocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `page_id` bigint(20) UNSIGNED NOT NULL,
  `block_key` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `settings_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings_json`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `page_block_translations`
--

CREATE TABLE `page_block_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `page_block_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `button_text` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `page_translations`
--

CREATE TABLE `page_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `page_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `contract_id` bigint(20) UNSIGNED NOT NULL,
  `payment_number` varchar(255) NOT NULL,
  `payment_type` varchar(255) NOT NULL DEFAULT 'contract_advance',
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `receipt_path` varchar(255) DEFAULT NULL,
  `receipt_original_name` varchar(255) DEFAULT NULL,
  `receipt_mime_type` varchar(255) DEFAULT NULL,
  `receipt_size` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permission_role`
--

CREATE TABLE `permission_role` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prikazes`
--

CREATE TABLE `prikazes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `enrollment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_profile_id` bigint(20) UNSIGNED NOT NULL,
  `program_id` bigint(20) UNSIGNED DEFAULT NULL,
  `prikaz_number` varchar(255) NOT NULL,
  `issue_date` date DEFAULT NULL,
  `academic_year` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'ISSUED',
  `document_path` varchar(255) DEFAULT NULL,
  `issued_by` bigint(20) UNSIGNED DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `faculty_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(255) NOT NULL,
  `code` varchar(255) DEFAULT NULL,
  `official_code` varchar(255) DEFAULT NULL,
  `track` varchar(255) DEFAULT NULL,
  `degree` varchar(255) NOT NULL,
  `duration_years` double NOT NULL,
  `study_mode` varchar(255) NOT NULL,
  `language_of_study` varchar(255) NOT NULL,
  `tuition_fee` decimal(10,2) NOT NULL,
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `program_courses`
--

CREATE TABLE `program_courses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `program_id` bigint(20) UNSIGNED NOT NULL,
  `course_id` bigint(20) UNSIGNED NOT NULL,
  `year` int(11) NOT NULL,
  `semester` int(11) NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `program_translations`
--

CREATE TABLE `program_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `program_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `documents` text DEFAULT NULL,
  `curriculum_summary` text DEFAULT NULL,
  `career_opportunities` text DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `residence_permit_processes`
--

CREATE TABLE `residence_permit_processes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `student_profile_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'NOT_STARTED',
  `notes` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `reviewer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT NULL,
  `expires_at` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_user`
--

CREATE TABLE `role_user` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `home_visible` tinyint(1) NOT NULL DEFAULT 1,
  `opens_new_tab` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_fee_payments`
--

CREATE TABLE `service_fee_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `payment_number` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 300.00,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `status` varchar(255) NOT NULL DEFAULT 'UPLOADED',
  `receipt_path` varchar(255) DEFAULT NULL,
  `receipt_original_name` varchar(255) DEFAULT NULL,
  `receipt_mime_type` varchar(255) DEFAULT NULL,
  `receipt_size` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_translations`
--

CREATE TABLE `service_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `service_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `action_label` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'text',
  `group` varchar(255) NOT NULL DEFAULT 'general',
  `is_public` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_profiles`
--

CREATE TABLE `staff_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `faculty_id` bigint(20) UNSIGNED DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_profile_translations`
--

CREATE TABLE `staff_profile_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `staff_profile_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `bio` text DEFAULT NULL,
  `office` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_profiles`
--

CREATE TABLE `student_profiles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `full_name_english` varchar(255) DEFAULT NULL,
  `phone` varchar(255) NOT NULL,
  `alternative_phone` varchar(255) DEFAULT NULL,
  `preferred_messenger` varchar(255) DEFAULT NULL,
  `telegram_username` varchar(255) DEFAULT NULL,
  `gender` varchar(255) NOT NULL,
  `birth_date` date NOT NULL,
  `country_of_birth` varchar(255) DEFAULT NULL,
  `place_of_birth` varchar(255) DEFAULT NULL,
  `passport_number` varchar(255) NOT NULL,
  `passport_type` varchar(255) DEFAULT NULL,
  `passport_issue_date` date DEFAULT NULL,
  `passport_expiry_date` date DEFAULT NULL,
  `passport_issuing_country` varchar(255) DEFAULT NULL,
  `passport_place_of_issue` varchar(255) DEFAULT NULL,
  `nationality` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_visa_processes`
--

CREATE TABLE `student_visa_processes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `application_id` bigint(20) UNSIGNED NOT NULL,
  `student_profile_id` bigint(20) UNSIGNED NOT NULL,
  `telex_number` varchar(255) DEFAULT NULL,
  `telex_status` varchar(255) NOT NULL DEFAULT 'NOT_STARTED',
  `visa_status` varchar(255) NOT NULL DEFAULT 'NOT_STARTED',
  `visa_notes` text DEFAULT NULL,
  `reviewer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `telex_issued_at` timestamp NULL DEFAULT NULL,
  `visa_updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `subject` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'open',
  `priority` varchar(255) NOT NULL DEFAULT 'normal',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_ticket_messages`
--

CREATE TABLE `support_ticket_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `support_ticket_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `translation_keys`
--

CREATE TABLE `translation_keys` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `group` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_system` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `translation_values`
--

CREATE TABLE `translation_values` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `translation_key_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `university_centers`
--

CREATE TABLE `university_centers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `university_center_settings`
--

CREATE TABLE `university_center_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `university_center_setting_translations`
--

CREATE TABLE `university_center_setting_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `center_setting_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(255) NOT NULL,
  `sidebar_title` varchar(255) NOT NULL,
  `structure_label` varchar(255) NOT NULL,
  `about_label` varchar(255) NOT NULL,
  `staff_label` varchar(255) NOT NULL,
  `default_head_desc` text NOT NULL,
  `mission_label` varchar(255) NOT NULL,
  `support_title` varchar(255) NOT NULL,
  `support_desc` text NOT NULL,
  `contact_btn_label` varchar(255) NOT NULL,
  `function_badge_label` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `university_center_translations`
--

CREATE TABLE `university_center_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `university_center_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `head` varchar(255) NOT NULL,
  `head_title` varchar(255) NOT NULL,
  `head_description` text DEFAULT NULL,
  `office_hours` varchar(255) NOT NULL,
  `about` text NOT NULL,
  `functions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`functions`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `slug` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `video_type` varchar(255) NOT NULL DEFAULT 'youtube',
  `youtube_id` varchar(255) DEFAULT NULL,
  `duration` varchar(255) DEFAULT NULL,
  `views_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `likes_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `video_comments`
--

CREATE TABLE `video_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `video_id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `author_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `is_approved` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `video_gallery_settings`
--

CREATE TABLE `video_gallery_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `home_limit` smallint(5) UNSIGNED NOT NULL DEFAULT 4,
  `subscriber_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `youtube_channel_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `video_gallery_setting_translations`
--

CREATE TABLE `video_gallery_setting_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `video_gallery_setting_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(255) NOT NULL,
  `home_tag` varchar(255) DEFAULT NULL,
  `home_title` varchar(255) DEFAULT NULL,
  `home_subtitle` text DEFAULT NULL,
  `view_all_label` varchar(255) DEFAULT NULL,
  `recommended_label` varchar(255) DEFAULT NULL,
  `videos_label` varchar(255) DEFAULT NULL,
  `description_title` varchar(255) DEFAULT NULL,
  `show_more_label` varchar(255) DEFAULT NULL,
  `show_less_label` varchar(255) DEFAULT NULL,
  `like_label` varchar(255) DEFAULT NULL,
  `liked_label` varchar(255) DEFAULT NULL,
  `share_label` varchar(255) DEFAULT NULL,
  `subscribe_label` varchar(255) DEFAULT NULL,
  `subscribed_label` varchar(255) DEFAULT NULL,
  `subscribers_label` varchar(255) DEFAULT NULL,
  `link_copied_label` varchar(255) DEFAULT NULL,
  `no_videos_label` varchar(255) DEFAULT NULL,
  `category_labels` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`category_labels`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `views_label` varchar(255) DEFAULT NULL,
  `channel_name` varchar(255) DEFAULT NULL,
  `comments_label` varchar(255) DEFAULT NULL,
  `reply_label` varchar(255) DEFAULT NULL,
  `form_title` varchar(255) DEFAULT NULL,
  `form_comment_label` varchar(255) DEFAULT NULL,
  `form_submit_label` varchar(255) DEFAULT NULL,
  `sign_in_title` varchar(255) DEFAULT NULL,
  `sign_in_text` varchar(255) DEFAULT NULL,
  `sign_in_action` varchar(255) DEFAULT NULL,
  `signed_in_as_label` varchar(255) DEFAULT NULL,
  `category_label` varchar(255) DEFAULT NULL,
  `duration_label` varchar(255) DEFAULT NULL,
  `platform_label` varchar(255) DEFAULT NULL,
  `local_label` varchar(255) DEFAULT NULL,
  `youtube_label` varchar(255) DEFAULT NULL,
  `playing_label` varchar(255) DEFAULT NULL,
  `verified_channel_label` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `video_translations`
--

CREATE TABLE `video_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `video_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `web_footers`
--

CREATE TABLE `web_footers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL DEFAULT 'main',
  `useful_links` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`useful_links`)),
  `faculty_links` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`faculty_links`)),
  `social_links` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`social_links`)),
  `admissions_apply_url` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `copyright_year` smallint(5) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `web_footer_translations`
--

CREATE TABLE `web_footer_translations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `web_footer_id` bigint(20) UNSIGNED NOT NULL,
  `locale` varchar(5) NOT NULL,
  `logo_alt` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `admissions_badge` varchar(255) DEFAULT NULL,
  `admissions_heading` varchar(255) DEFAULT NULL,
  `admissions_description` text DEFAULT NULL,
  `admissions_button_label` varchar(255) DEFAULT NULL,
  `newsletter_title` varchar(255) DEFAULT NULL,
  `newsletter_description` text DEFAULT NULL,
  `newsletter_placeholder` varchar(255) DEFAULT NULL,
  `newsletter_success_message` varchar(255) DEFAULT NULL,
  `useful_links_title` varchar(255) DEFAULT NULL,
  `faculties_title` varchar(255) DEFAULT NULL,
  `contact_title` varchar(255) DEFAULT NULL,
  `address_line_1` varchar(255) DEFAULT NULL,
  `address_line_2` varchar(255) DEFAULT NULL,
  `phone_label` varchar(255) DEFAULT NULL,
  `email_label` varchar(255) DEFAULT NULL,
  `rights_text` varchar(255) DEFAULT NULL,
  `useful_link_labels` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`useful_link_labels`)),
  `faculty_link_labels` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`faculty_link_labels`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about_pages`
--
ALTER TABLE `about_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `about_pages_key_unique` (`key`);

--
-- Indexes for table `about_page_content_entries`
--
ALTER TABLE `about_page_content_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `about_page_content_entries_about_page_id_path_unique` (`about_page_id`,`path`);

--
-- Indexes for table `about_page_content_entry_translations`
--
ALTER TABLE `about_page_content_entry_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `about_content_entry_locale_unique` (`about_page_content_entry_id`,`locale`);

--
-- Indexes for table `about_page_translations`
--
ALTER TABLE `about_page_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `about_page_translations_about_page_id_locale_unique` (`about_page_id`,`locale`);

--
-- Indexes for table `administration_profiles`
--
ALTER TABLE `administration_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `administration_profiles_slug_unique` (`slug`);

--
-- Indexes for table `administration_profile_translations`
--
ALTER TABLE `administration_profile_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_profile_locale_unique` (`administration_profile_id`,`locale`);

--
-- Indexes for table `administration_settings`
--
ALTER TABLE `administration_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `administration_settings_key_unique` (`key`);

--
-- Indexes for table `administration_setting_translations`
--
ALTER TABLE `administration_setting_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_setting_locale_unique` (`administration_setting_id`,`locale`);

--
-- Indexes for table `admissions`
--
ALTER TABLE `admissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admissions_application_id_unique` (`application_id`),
  ADD UNIQUE KEY `admissions_admission_number_unique` (`admission_number`),
  ADD KEY `admissions_student_profile_id_foreign` (`student_profile_id`),
  ADD KEY `admissions_faculty_id_foreign` (`faculty_id`),
  ADD KEY `admissions_program_id_foreign` (`program_id`),
  ADD KEY `admissions_issued_by_foreign` (`issued_by`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `announcements_slug_unique` (`slug`),
  ADD KEY `announcements_public_order_idx` (`is_published`,`priority`,`created_at`),
  ADD KEY `announcements_active_idx` (`is_published`,`ends_at`);

--
-- Indexes for table `announcement_settings`
--
ALTER TABLE `announcement_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `announcement_settings_key_unique` (`key`);

--
-- Indexes for table `announcement_setting_translations`
--
ALTER TABLE `announcement_setting_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ann_setting_locale_unique` (`announcement_setting_id`,`locale`);

--
-- Indexes for table `announcement_translations`
--
ALTER TABLE `announcement_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `announcement_translations_announcement_id_locale_unique` (`announcement_id`,`locale`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `applications_application_number_unique` (`application_number`),
  ADD KEY `applications_student_status_date_idx` (`student_profile_id`,`status`,`created_at`),
  ADD KEY `applications_program_status_idx` (`program_id`,`status`),
  ADD KEY `applications_faculty_status_idx` (`faculty_id`,`status`),
  ADD KEY `applications_department_status_idx` (`department_id`,`status`),
  ADD KEY `applications_final_reviewed_by_foreign` (`final_reviewed_by`);

--
-- Indexes for table `application_countries`
--
ALTER TABLE `application_countries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_countries_name_unique` (`name`),
  ADD UNIQUE KEY `application_countries_code_unique` (`code`);

--
-- Indexes for table `application_documents`
--
ALTER TABLE `application_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `app_docs_application_type_status_idx` (`application_id`,`document_type`,`status`),
  ADD KEY `application_documents_student_profile_id_foreign` (`student_profile_id`),
  ADD KEY `application_documents_reviewer_id_foreign` (`reviewer_id`),
  ADD KEY `application_documents_previous_document_id_foreign` (`previous_document_id`);

--
-- Indexes for table `application_equivalencies`
--
ALTER TABLE `application_equivalencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_equivalencies_application_id_unique` (`application_id`),
  ADD KEY `application_equivalencies_reviewer_id_foreign` (`reviewer_id`);

--
-- Indexes for table `application_fee_payments`
--
ALTER TABLE `application_fee_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_fee_payments_payment_number_unique` (`payment_number`),
  ADD KEY `application_fee_payments_application_id_foreign` (`application_id`),
  ADD KEY `application_fee_payments_reviewer_id_foreign` (`reviewer_id`);

--
-- Indexes for table `application_nationalities`
--
ALTER TABLE `application_nationalities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `application_nationalities_name_unique` (`name`),
  ADD UNIQUE KEY `application_nationalities_code_unique` (`code`);

--
-- Indexes for table `application_status_histories`
--
ALTER TABLE `application_status_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_status_histories_changed_by_foreign` (`changed_by`),
  ADD KEY `app_status_application_date_idx` (`application_id`,`created_at`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `audit_logs_user_date_idx` (`user_id`,`created_at`),
  ADD KEY `audit_logs_model_idx` (`model_type`,`model_id`);

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blogs_slug_unique` (`slug`),
  ADD KEY `blogs_category_index` (`category`),
  ADD KEY `blogs_published_at_index` (`published_at`),
  ADD KEY `blogs_is_published_index` (`is_published`);

--
-- Indexes for table `blog_comments`
--
ALTER TABLE `blog_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blog_comments_blog_id_foreign` (`blog_id`),
  ADD KEY `blog_comments_parent_id_foreign` (`parent_id`),
  ADD KEY `blog_comments_is_approved_index` (`is_approved`);

--
-- Indexes for table `blog_settings`
--
ALTER TABLE `blog_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blog_settings_key_unique` (`key`);

--
-- Indexes for table `blog_setting_translations`
--
ALTER TABLE `blog_setting_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blog_setting_translations_blog_setting_id_locale_unique` (`blog_setting_id`,`locale`),
  ADD KEY `blog_setting_translations_locale_index` (`locale`);

--
-- Indexes for table `blog_translations`
--
ALTER TABLE `blog_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `blog_translations_blog_id_locale_unique` (`blog_id`,`locale`),
  ADD KEY `blog_translations_locale_index` (`locale`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comments_commentable_type_commentable_id_index` (`commentable_type`,`commentable_id`),
  ADD KEY `comments_user_id_foreign` (`user_id`);

--
-- Indexes for table `contact_pages`
--
ALTER TABLE `contact_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contact_pages_key_unique` (`key`);

--
-- Indexes for table `contact_page_translations`
--
ALTER TABLE `contact_page_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contact_page_translations_contact_page_id_locale_unique` (`contact_page_id`,`locale`);

--
-- Indexes for table `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contracts_contract_number_unique` (`contract_number`),
  ADD KEY `contracts_application_status_idx` (`application_id`,`status`),
  ADD KEY `contracts_issued_by_foreign` (`issued_by`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `courses_code_unique` (`code`);

--
-- Indexes for table `course_translations`
--
ALTER TABLE `course_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_translations_course_id_locale_unique` (`course_id`,`locale`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `departments_slug_unique` (`slug`),
  ADD UNIQUE KEY `departments_code_unique` (`code`),
  ADD KEY `departments_faculty_active_sort_idx` (`faculty_id`,`is_active`,`sort_order`),
  ADD KEY `departments_active_sort_idx` (`is_active`,`sort_order`);

--
-- Indexes for table `department_translations`
--
ALTER TABLE `department_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `department_translations_department_id_locale_unique` (`department_id`,`locale`);

--
-- Indexes for table `document_requests`
--
ALTER TABLE `document_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doc_requests_student_status_idx` (`student_profile_id`,`status`);

--
-- Indexes for table `document_requirements`
--
ALTER TABLE `document_requirements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_requirements_application_id_foreign` (`application_id`),
  ADD KEY `document_requirements_program_id_foreign` (`program_id`),
  ADD KEY `document_requirements_requested_by_foreign` (`requested_by`);

--
-- Indexes for table `education_backgrounds`
--
ALTER TABLE `education_backgrounds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `education_backgrounds_student_profile_id_foreign` (`student_profile_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `enrollments_student_number_unique` (`student_number`),
  ADD UNIQUE KEY `enrollments_application_id_unique` (`application_id`),
  ADD KEY `enrollments_student_profile_id_foreign` (`student_profile_id`),
  ADD KEY `enrollments_program_id_foreign` (`program_id`),
  ADD KEY `enrollments_admission_id_foreign` (`admission_id`),
  ADD KEY `enrollments_issued_by_foreign` (`issued_by`);

--
-- Indexes for table `equivalency_courses`
--
ALTER TABLE `equivalency_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equivalency_courses_application_equivalency_id_foreign` (`application_equivalency_id`);

--
-- Indexes for table `faculties`
--
ALTER TABLE `faculties`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `faculties_slug_unique` (`slug`),
  ADD UNIQUE KEY `faculties_code_unique` (`code`),
  ADD KEY `faculties_active_sort_idx` (`is_active`,`sort_order`);

--
-- Indexes for table `faculty_translations`
--
ALTER TABLE `faculty_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `faculty_translations_faculty_id_locale_unique` (`faculty_id`,`locale`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `green_campus_articles`
--
ALTER TABLE `green_campus_articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `green_campus_articles_slug_unique` (`slug`),
  ADD KEY `green_articles_created_idx` (`created_at`);

--
-- Indexes for table `green_campus_article_translations`
--
ALTER TABLE `green_campus_article_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gc_art_id_locale_unique` (`green_campus_article_id`,`locale`);

--
-- Indexes for table `green_campus_settings`
--
ALTER TABLE `green_campus_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `green_campus_settings_key_unique` (`key`);

--
-- Indexes for table `green_campus_setting_translations`
--
ALTER TABLE `green_campus_setting_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gc_setting_locale_unique` (`green_campus_setting_id`,`locale`);

--
-- Indexes for table `green_campus_stats`
--
ALTER TABLE `green_campus_stats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `green_stats_sort_idx` (`sort_order`);

--
-- Indexes for table `green_campus_stat_translations`
--
ALTER TABLE `green_campus_stat_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gc_stat_id_locale_unique` (`green_campus_stat_id`,`locale`);

--
-- Indexes for table `guardians`
--
ALTER TABLE `guardians`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guardians_student_profile_id_foreign` (`student_profile_id`);

--
-- Indexes for table `housing_requests`
--
ALTER TABLE `housing_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `housing_requests_application_id_unique` (`application_id`),
  ADD KEY `housing_requests_student_profile_id_foreign` (`student_profile_id`),
  ADD KEY `housing_requests_reviewer_id_foreign` (`reviewer_id`);

--
-- Indexes for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inquiries_status_date_idx` (`status`,`created_at`);

--
-- Indexes for table `interactive_service_settings`
--
ALTER TABLE `interactive_service_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `interactive_service_settings_key_unique` (`key`);

--
-- Indexes for table `interactive_service_setting_translations`
--
ALTER TABLE `interactive_service_setting_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `interactive_service_setting_locale_unique` (`interactive_service_setting_id`,`locale`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `locales`
--
ALTER TABLE `locales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `locales_code_unique` (`code`);

--
-- Indexes for table `media`
--
ALTER TABLE `media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `media_public_type_idx` (`is_public`,`type`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `menus_key_unique` (`key`),
  ADD KEY `menus_location_active_idx` (`location`,`is_active`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menu_items_parent_id_foreign` (`parent_id`),
  ADD KEY `menu_items_tree_active_sort_idx` (`menu_id`,`parent_id`,`is_active`,`sort_order`);

--
-- Indexes for table `menu_item_translations`
--
ALTER TABLE `menu_item_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `menu_item_translations_menu_item_id_locale_unique` (`menu_item_id`,`locale`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `news_slug_unique` (`slug`),
  ADD KEY `news_published_date_idx` (`is_published`,`published_at`),
  ADD KEY `news_category_published_date_idx` (`category`,`is_published`,`published_at`);

--
-- Indexes for table `newsletter_subscriptions`
--
ALTER TABLE `newsletter_subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `newsletter_subscriptions_email_unique` (`email`);

--
-- Indexes for table `news_event_settings`
--
ALTER TABLE `news_event_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `news_event_settings_key_unique` (`key`);

--
-- Indexes for table `news_event_setting_translations`
--
ALTER TABLE `news_event_setting_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `news_event_settings_locale_unique` (`news_event_setting_id`,`locale`);

--
-- Indexes for table `news_translations`
--
ALTER TABLE `news_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `news_translations_news_id_locale_unique` (`news_id`,`locale`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_user_read_date_idx` (`user_id`,`is_read`,`created_at`),
  ADD KEY `notifications_related_application_id_foreign` (`related_application_id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pages_slug_unique` (`slug`),
  ADD KEY `pages_published_sort_idx` (`is_published`,`sort_order`);

--
-- Indexes for table `page_blocks`
--
ALTER TABLE `page_blocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `page_blocks_page_active_sort_idx` (`page_id`,`is_active`,`sort_order`);

--
-- Indexes for table `page_block_translations`
--
ALTER TABLE `page_block_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `page_block_translations_page_block_id_locale_unique` (`page_block_id`,`locale`);

--
-- Indexes for table `page_translations`
--
ALTER TABLE `page_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `page_translations_page_id_locale_unique` (`page_id`,`locale`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payments_payment_number_unique` (`payment_number`),
  ADD KEY `payments_contract_status_date_idx` (`contract_id`,`status`,`payment_date`),
  ADD KEY `payments_reviewer_id_foreign` (`reviewer_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`),
  ADD UNIQUE KEY `permissions_slug_unique` (`slug`);

--
-- Indexes for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `permission_role_role_id_foreign` (`role_id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `prikazes`
--
ALTER TABLE `prikazes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prikazes_application_id_unique` (`application_id`),
  ADD UNIQUE KEY `prikazes_prikaz_number_unique` (`prikaz_number`),
  ADD KEY `prikazes_enrollment_id_foreign` (`enrollment_id`),
  ADD KEY `prikazes_student_profile_id_foreign` (`student_profile_id`),
  ADD KEY `prikazes_program_id_foreign` (`program_id`),
  ADD KEY `prikazes_issued_by_foreign` (`issued_by`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `programs_slug_unique` (`slug`),
  ADD UNIQUE KEY `programs_code_unique` (`code`),
  ADD KEY `programs_faculty_active_sort_idx` (`faculty_id`,`is_active`,`sort_order`),
  ADD KEY `programs_department_active_sort_idx` (`department_id`,`is_active`,`sort_order`),
  ADD KEY `programs_official_track_idx` (`official_code`,`track`);

--
-- Indexes for table `program_courses`
--
ALTER TABLE `program_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `program_courses_program_id_foreign` (`program_id`),
  ADD KEY `program_courses_course_id_foreign` (`course_id`);

--
-- Indexes for table `program_translations`
--
ALTER TABLE `program_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `program_translations_program_id_locale_unique` (`program_id`,`locale`);

--
-- Indexes for table `residence_permit_processes`
--
ALTER TABLE `residence_permit_processes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `residence_permit_processes_application_id_unique` (`application_id`),
  ADD KEY `residence_permit_processes_student_profile_id_foreign` (`student_profile_id`),
  ADD KEY `residence_permit_processes_reviewer_id_foreign` (`reviewer_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`),
  ADD UNIQUE KEY `roles_slug_unique` (`slug`);

--
-- Indexes for table `role_user`
--
ALTER TABLE `role_user`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_user_role_id_foreign` (`role_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `services_slug_unique` (`slug`),
  ADD KEY `services_active_sort_idx` (`is_active`,`sort_order`);

--
-- Indexes for table `service_fee_payments`
--
ALTER TABLE `service_fee_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_fee_payments_payment_number_unique` (`payment_number`),
  ADD KEY `service_fee_payments_application_id_foreign` (`application_id`),
  ADD KEY `service_fee_payments_reviewer_id_foreign` (`reviewer_id`);

--
-- Indexes for table `service_translations`
--
ALTER TABLE `service_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `service_translations_service_id_locale_unique` (`service_id`,`locale`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`),
  ADD KEY `settings_public_group_idx` (`is_public`,`group`);

--
-- Indexes for table `staff_profiles`
--
ALTER TABLE `staff_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_profiles_slug_unique` (`slug`),
  ADD KEY `staff_faculty_active_sort_idx` (`faculty_id`,`is_active`,`sort_order`),
  ADD KEY `staff_department_active_sort_idx` (`department_id`,`is_active`,`sort_order`);

--
-- Indexes for table `staff_profile_translations`
--
ALTER TABLE `staff_profile_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staff_profile_translations_staff_profile_id_locale_unique` (`staff_profile_id`,`locale`);

--
-- Indexes for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_profiles_user_passport_idx` (`user_id`,`passport_number`);

--
-- Indexes for table `student_visa_processes`
--
ALTER TABLE `student_visa_processes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_visa_processes_application_id_unique` (`application_id`),
  ADD KEY `student_visa_processes_student_profile_id_foreign` (`student_profile_id`),
  ADD KEY `student_visa_processes_reviewer_id_foreign` (`reviewer_id`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `support_tickets_user_status_date_idx` (`user_id`,`status`,`created_at`);

--
-- Indexes for table `support_ticket_messages`
--
ALTER TABLE `support_ticket_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `support_ticket_messages_user_id_foreign` (`user_id`),
  ADD KEY `support_messages_ticket_date_idx` (`support_ticket_id`,`created_at`);

--
-- Indexes for table `translation_keys`
--
ALTER TABLE `translation_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `translation_keys_group_key_unique` (`group`,`key`);

--
-- Indexes for table `translation_values`
--
ALTER TABLE `translation_values`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `translation_values_translation_key_id_locale_unique` (`translation_key_id`,`locale`);

--
-- Indexes for table `university_centers`
--
ALTER TABLE `university_centers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `university_centers_slug_unique` (`slug`);

--
-- Indexes for table `university_center_settings`
--
ALTER TABLE `university_center_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `university_center_setting_translations`
--
ALTER TABLE `university_center_setting_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ucs_translation_unique` (`center_setting_id`,`locale`);

--
-- Indexes for table `university_center_translations`
--
ALTER TABLE `university_center_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uc_translation_unique` (`university_center_id`,`locale`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `videos_slug_unique` (`slug`),
  ADD KEY `videos_active_sort_idx` (`is_active`,`sort_order`);

--
-- Indexes for table `video_comments`
--
ALTER TABLE `video_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `video_comments_video_id_foreign` (`video_id`),
  ADD KEY `video_comments_parent_id_foreign` (`parent_id`);

--
-- Indexes for table `video_gallery_settings`
--
ALTER TABLE `video_gallery_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `video_gallery_settings_key_unique` (`key`);

--
-- Indexes for table `video_gallery_setting_translations`
--
ALTER TABLE `video_gallery_setting_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `video_gallery_setting_locale_unique` (`video_gallery_setting_id`,`locale`);

--
-- Indexes for table `video_translations`
--
ALTER TABLE `video_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `video_translations_video_id_locale_unique` (`video_id`,`locale`);

--
-- Indexes for table `web_footers`
--
ALTER TABLE `web_footers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `web_footers_key_unique` (`key`);

--
-- Indexes for table `web_footer_translations`
--
ALTER TABLE `web_footer_translations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `web_footer_translations_web_footer_id_locale_unique` (`web_footer_id`,`locale`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `about_pages`
--
ALTER TABLE `about_pages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `about_page_content_entries`
--
ALTER TABLE `about_page_content_entries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=185;

--
-- AUTO_INCREMENT for table `about_page_content_entry_translations`
--
ALTER TABLE `about_page_content_entry_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=737;

--
-- AUTO_INCREMENT for table `about_page_translations`
--
ALTER TABLE `about_page_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `administration_profiles`
--
ALTER TABLE `administration_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `administration_profile_translations`
--
ALTER TABLE `administration_profile_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `administration_settings`
--
ALTER TABLE `administration_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `administration_setting_translations`
--
ALTER TABLE `administration_setting_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `admissions`
--
ALTER TABLE `admissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `announcement_settings`
--
ALTER TABLE `announcement_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `announcement_setting_translations`
--
ALTER TABLE `announcement_setting_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `announcement_translations`
--
ALTER TABLE `announcement_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `application_countries`
--
ALTER TABLE `application_countries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `application_documents`
--
ALTER TABLE `application_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `application_equivalencies`
--
ALTER TABLE `application_equivalencies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `application_fee_payments`
--
ALTER TABLE `application_fee_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `application_nationalities`
--
ALTER TABLE `application_nationalities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `application_status_histories`
--
ALTER TABLE `application_status_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=274;

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `blog_comments`
--
ALTER TABLE `blog_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `blog_settings`
--
ALTER TABLE `blog_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `blog_setting_translations`
--
ALTER TABLE `blog_setting_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `blog_translations`
--
ALTER TABLE `blog_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact_pages`
--
ALTER TABLE `contact_pages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contact_page_translations`
--
ALTER TABLE `contact_page_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `contracts`
--
ALTER TABLE `contracts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=208;

--
-- AUTO_INCREMENT for table `course_translations`
--
ALTER TABLE `course_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=829;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `department_translations`
--
ALTER TABLE `department_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=139;

--
-- AUTO_INCREMENT for table `document_requests`
--
ALTER TABLE `document_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_requirements`
--
ALTER TABLE `document_requirements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `education_backgrounds`
--
ALTER TABLE `education_backgrounds`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `equivalency_courses`
--
ALTER TABLE `equivalency_courses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faculties`
--
ALTER TABLE `faculties`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `faculty_translations`
--
ALTER TABLE `faculty_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `green_campus_articles`
--
ALTER TABLE `green_campus_articles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `green_campus_article_translations`
--
ALTER TABLE `green_campus_article_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `green_campus_settings`
--
ALTER TABLE `green_campus_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `green_campus_setting_translations`
--
ALTER TABLE `green_campus_setting_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `green_campus_stats`
--
ALTER TABLE `green_campus_stats`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `green_campus_stat_translations`
--
ALTER TABLE `green_campus_stat_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `guardians`
--
ALTER TABLE `guardians`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `housing_requests`
--
ALTER TABLE `housing_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inquiries`
--
ALTER TABLE `inquiries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `interactive_service_settings`
--
ALTER TABLE `interactive_service_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `interactive_service_setting_translations`
--
ALTER TABLE `interactive_service_setting_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locales`
--
ALTER TABLE `locales`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `media`
--
ALTER TABLE `media`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=416;

--
-- AUTO_INCREMENT for table `menu_item_translations`
--
ALTER TABLE `menu_item_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1661;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `newsletter_subscriptions`
--
ALTER TABLE `newsletter_subscriptions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `news_event_settings`
--
ALTER TABLE `news_event_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `news_event_setting_translations`
--
ALTER TABLE `news_event_setting_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `news_translations`
--
ALTER TABLE `news_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=245;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `page_blocks`
--
ALTER TABLE `page_blocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `page_block_translations`
--
ALTER TABLE `page_block_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `page_translations`
--
ALTER TABLE `page_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `prikazes`
--
ALTER TABLE `prikazes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `programs`
--
ALTER TABLE `programs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `program_courses`
--
ALTER TABLE `program_courses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=698;

--
-- AUTO_INCREMENT for table `program_translations`
--
ALTER TABLE `program_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=477;

--
-- AUTO_INCREMENT for table `residence_permit_processes`
--
ALTER TABLE `residence_permit_processes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `service_fee_payments`
--
ALTER TABLE `service_fee_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `service_translations`
--
ALTER TABLE `service_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT for table `staff_profiles`
--
ALTER TABLE `staff_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=590;

--
-- AUTO_INCREMENT for table `staff_profile_translations`
--
ALTER TABLE `staff_profile_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2330;

--
-- AUTO_INCREMENT for table `student_profiles`
--
ALTER TABLE `student_profiles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_visa_processes`
--
ALTER TABLE `student_visa_processes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_ticket_messages`
--
ALTER TABLE `support_ticket_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `translation_keys`
--
ALTER TABLE `translation_keys`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2471;

--
-- AUTO_INCREMENT for table `translation_values`
--
ALTER TABLE `translation_values`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10189;

--
-- AUTO_INCREMENT for table `university_centers`
--
ALTER TABLE `university_centers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `university_center_settings`
--
ALTER TABLE `university_center_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `university_center_setting_translations`
--
ALTER TABLE `university_center_setting_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `university_center_translations`
--
ALTER TABLE `university_center_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `video_comments`
--
ALTER TABLE `video_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `video_gallery_settings`
--
ALTER TABLE `video_gallery_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `video_gallery_setting_translations`
--
ALTER TABLE `video_gallery_setting_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `video_translations`
--
ALTER TABLE `video_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT for table `web_footers`
--
ALTER TABLE `web_footers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `web_footer_translations`
--
ALTER TABLE `web_footer_translations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `about_page_content_entries`
--
ALTER TABLE `about_page_content_entries`
  ADD CONSTRAINT `about_page_content_entries_about_page_id_foreign` FOREIGN KEY (`about_page_id`) REFERENCES `about_pages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `about_page_content_entry_translations`
--
ALTER TABLE `about_page_content_entry_translations`
  ADD CONSTRAINT `about_content_entry_fk` FOREIGN KEY (`about_page_content_entry_id`) REFERENCES `about_page_content_entries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `about_page_translations`
--
ALTER TABLE `about_page_translations`
  ADD CONSTRAINT `about_page_translations_about_page_id_foreign` FOREIGN KEY (`about_page_id`) REFERENCES `about_pages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `administration_profile_translations`
--
ALTER TABLE `administration_profile_translations`
  ADD CONSTRAINT `admin_profile_translation_fk` FOREIGN KEY (`administration_profile_id`) REFERENCES `administration_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `administration_setting_translations`
--
ALTER TABLE `administration_setting_translations`
  ADD CONSTRAINT `admin_setting_translation_fk` FOREIGN KEY (`administration_setting_id`) REFERENCES `administration_settings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admissions`
--
ALTER TABLE `admissions`
  ADD CONSTRAINT `admissions_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `admissions_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `admissions_issued_by_foreign` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `admissions_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `admissions_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `announcement_setting_translations`
--
ALTER TABLE `announcement_setting_translations`
  ADD CONSTRAINT `ann_setting_trans_setting_fk` FOREIGN KEY (`announcement_setting_id`) REFERENCES `announcement_settings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `announcement_translations`
--
ALTER TABLE `announcement_translations`
  ADD CONSTRAINT `announcement_translations_announcement_id_foreign` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `applications_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `applications_final_reviewed_by_foreign` FOREIGN KEY (`final_reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `applications_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `applications_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `application_documents`
--
ALTER TABLE `application_documents`
  ADD CONSTRAINT `application_documents_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `application_documents_previous_document_id_foreign` FOREIGN KEY (`previous_document_id`) REFERENCES `application_documents` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `application_documents_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `application_documents_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `application_equivalencies`
--
ALTER TABLE `application_equivalencies`
  ADD CONSTRAINT `application_equivalencies_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `application_equivalencies_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `application_fee_payments`
--
ALTER TABLE `application_fee_payments`
  ADD CONSTRAINT `application_fee_payments_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `application_fee_payments_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `application_status_histories`
--
ALTER TABLE `application_status_histories`
  ADD CONSTRAINT `application_status_histories_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `application_status_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `blog_comments`
--
ALTER TABLE `blog_comments`
  ADD CONSTRAINT `blog_comments_blog_id_foreign` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `blog_comments_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `blog_comments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blog_setting_translations`
--
ALTER TABLE `blog_setting_translations`
  ADD CONSTRAINT `blog_setting_translations_blog_setting_id_foreign` FOREIGN KEY (`blog_setting_id`) REFERENCES `blog_settings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `blog_translations`
--
ALTER TABLE `blog_translations`
  ADD CONSTRAINT `blog_translations_blog_id_foreign` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `contact_page_translations`
--
ALTER TABLE `contact_page_translations`
  ADD CONSTRAINT `contact_page_translations_contact_page_id_foreign` FOREIGN KEY (`contact_page_id`) REFERENCES `contact_pages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contracts`
--
ALTER TABLE `contracts`
  ADD CONSTRAINT `contracts_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `contracts_issued_by_foreign` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `course_translations`
--
ALTER TABLE `course_translations`
  ADD CONSTRAINT `course_translations_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `department_translations`
--
ALTER TABLE `department_translations`
  ADD CONSTRAINT `department_translations_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_requests`
--
ALTER TABLE `document_requests`
  ADD CONSTRAINT `document_requests_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_requirements`
--
ALTER TABLE `document_requirements`
  ADD CONSTRAINT `document_requirements_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `document_requirements_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `document_requirements_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `education_backgrounds`
--
ALTER TABLE `education_backgrounds`
  ADD CONSTRAINT `education_backgrounds_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_admission_id_foreign` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `enrollments_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_issued_by_foreign` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `enrollments_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `equivalency_courses`
--
ALTER TABLE `equivalency_courses`
  ADD CONSTRAINT `equivalency_courses_application_equivalency_id_foreign` FOREIGN KEY (`application_equivalency_id`) REFERENCES `application_equivalencies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `faculty_translations`
--
ALTER TABLE `faculty_translations`
  ADD CONSTRAINT `faculty_translations_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `green_campus_article_translations`
--
ALTER TABLE `green_campus_article_translations`
  ADD CONSTRAINT `gc_art_foreign` FOREIGN KEY (`green_campus_article_id`) REFERENCES `green_campus_articles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `green_campus_setting_translations`
--
ALTER TABLE `green_campus_setting_translations`
  ADD CONSTRAINT `gc_setting_foreign` FOREIGN KEY (`green_campus_setting_id`) REFERENCES `green_campus_settings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `green_campus_stat_translations`
--
ALTER TABLE `green_campus_stat_translations`
  ADD CONSTRAINT `gc_stat_foreign` FOREIGN KEY (`green_campus_stat_id`) REFERENCES `green_campus_stats` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `guardians`
--
ALTER TABLE `guardians`
  ADD CONSTRAINT `guardians_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `housing_requests`
--
ALTER TABLE `housing_requests`
  ADD CONSTRAINT `housing_requests_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `housing_requests_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `housing_requests_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `interactive_service_setting_translations`
--
ALTER TABLE `interactive_service_setting_translations`
  ADD CONSTRAINT `int_service_setting_tr_setting_fk` FOREIGN KEY (`interactive_service_setting_id`) REFERENCES `interactive_service_settings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `menu_items_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_item_translations`
--
ALTER TABLE `menu_item_translations`
  ADD CONSTRAINT `menu_item_translations_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `news_event_setting_translations`
--
ALTER TABLE `news_event_setting_translations`
  ADD CONSTRAINT `news_event_setting_translations_news_event_setting_id_foreign` FOREIGN KEY (`news_event_setting_id`) REFERENCES `news_event_settings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `news_translations`
--
ALTER TABLE `news_translations`
  ADD CONSTRAINT `news_translations_news_id_foreign` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_related_application_id_foreign` FOREIGN KEY (`related_application_id`) REFERENCES `applications` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `page_blocks`
--
ALTER TABLE `page_blocks`
  ADD CONSTRAINT `page_blocks_page_id_foreign` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `page_block_translations`
--
ALTER TABLE `page_block_translations`
  ADD CONSTRAINT `page_block_translations_page_block_id_foreign` FOREIGN KEY (`page_block_id`) REFERENCES `page_blocks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `page_translations`
--
ALTER TABLE `page_translations`
  ADD CONSTRAINT `page_translations_page_id_foreign` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_contract_id_foreign` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `permission_role`
--
ALTER TABLE `permission_role`
  ADD CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `prikazes`
--
ALTER TABLE `prikazes`
  ADD CONSTRAINT `prikazes_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prikazes_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `prikazes_issued_by_foreign` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `prikazes_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `prikazes_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `programs`
--
ALTER TABLE `programs`
  ADD CONSTRAINT `programs_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `programs_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `program_courses`
--
ALTER TABLE `program_courses`
  ADD CONSTRAINT `program_courses_course_id_foreign` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `program_courses_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `program_translations`
--
ALTER TABLE `program_translations`
  ADD CONSTRAINT `program_translations_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `residence_permit_processes`
--
ALTER TABLE `residence_permit_processes`
  ADD CONSTRAINT `residence_permit_processes_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `residence_permit_processes_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `residence_permit_processes_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_user`
--
ALTER TABLE `role_user`
  ADD CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_fee_payments`
--
ALTER TABLE `service_fee_payments`
  ADD CONSTRAINT `service_fee_payments_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_fee_payments_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `service_translations`
--
ALTER TABLE `service_translations`
  ADD CONSTRAINT `service_translations_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_profiles`
--
ALTER TABLE `staff_profiles`
  ADD CONSTRAINT `staff_profiles_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `staff_profiles_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_profile_translations`
--
ALTER TABLE `staff_profile_translations`
  ADD CONSTRAINT `staff_profile_translations_staff_profile_id_foreign` FOREIGN KEY (`staff_profile_id`) REFERENCES `staff_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD CONSTRAINT `student_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_visa_processes`
--
ALTER TABLE `student_visa_processes`
  ADD CONSTRAINT `student_visa_processes_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_visa_processes_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `student_visa_processes_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_ticket_messages`
--
ALTER TABLE `support_ticket_messages`
  ADD CONSTRAINT `support_ticket_messages_support_ticket_id_foreign` FOREIGN KEY (`support_ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `support_ticket_messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `translation_values`
--
ALTER TABLE `translation_values`
  ADD CONSTRAINT `translation_values_translation_key_id_foreign` FOREIGN KEY (`translation_key_id`) REFERENCES `translation_keys` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `university_center_setting_translations`
--
ALTER TABLE `university_center_setting_translations`
  ADD CONSTRAINT `university_center_setting_translations_center_setting_id_foreign` FOREIGN KEY (`center_setting_id`) REFERENCES `university_center_settings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `university_center_translations`
--
ALTER TABLE `university_center_translations`
  ADD CONSTRAINT `university_center_translations_university_center_id_foreign` FOREIGN KEY (`university_center_id`) REFERENCES `university_centers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `video_comments`
--
ALTER TABLE `video_comments`
  ADD CONSTRAINT `video_comments_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `video_comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `video_comments_video_id_foreign` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `video_gallery_setting_translations`
--
ALTER TABLE `video_gallery_setting_translations`
  ADD CONSTRAINT `vgs_trans_setting_fk` FOREIGN KEY (`video_gallery_setting_id`) REFERENCES `video_gallery_settings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `video_translations`
--
ALTER TABLE `video_translations`
  ADD CONSTRAINT `video_translations_video_id_foreign` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `web_footer_translations`
--
ALTER TABLE `web_footer_translations`
  ADD CONSTRAINT `web_footer_translations_web_footer_id_foreign` FOREIGN KEY (`web_footer_id`) REFERENCES `web_footers` (`id`) ON DELETE CASCADE;

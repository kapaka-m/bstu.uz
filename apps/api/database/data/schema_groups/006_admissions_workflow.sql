-- Admissions Workflow tables generated from bstu_international

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `student_profiles`;

CREATE TABLE `student_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_profiles_user_passport_idx` (`user_id`,`passport_number`),
  CONSTRAINT `student_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `applications`;

CREATE TABLE `applications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `application_number` varchar(255) DEFAULT NULL,
  `student_profile_id` bigint(20) unsigned NOT NULL,
  `program_id` bigint(20) unsigned NOT NULL,
  `faculty_id` bigint(20) unsigned DEFAULT NULL,
  `department_id` bigint(20) unsigned DEFAULT NULL,
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
  `final_reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `final_reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `correction_reason` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `applications_application_number_unique` (`application_number`),
  KEY `applications_student_status_date_idx` (`student_profile_id`,`status`,`created_at`),
  KEY `applications_program_status_idx` (`program_id`,`status`),
  KEY `applications_faculty_status_idx` (`faculty_id`,`status`),
  KEY `applications_department_status_idx` (`department_id`,`status`),
  KEY `applications_final_reviewed_by_foreign` (`final_reviewed_by`),
  CONSTRAINT `applications_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `applications_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE SET NULL,
  CONSTRAINT `applications_final_reviewed_by_foreign` FOREIGN KEY (`final_reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `applications_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `applications_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `admissions`;

CREATE TABLE `admissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `application_id` bigint(20) unsigned NOT NULL,
  `student_profile_id` bigint(20) unsigned NOT NULL,
  `faculty_id` bigint(20) unsigned DEFAULT NULL,
  `program_id` bigint(20) unsigned DEFAULT NULL,
  `admission_number` varchar(255) NOT NULL,
  `issue_date` date NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'ISSUED',
  `student_type` varchar(255) DEFAULT NULL,
  `education_type` varchar(255) DEFAULT NULL,
  `study_language` varchar(255) DEFAULT NULL,
  `estimated_study_duration` varchar(255) DEFAULT NULL,
  `document_path` varchar(255) DEFAULT NULL,
  `issued_by` bigint(20) unsigned DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admissions_application_id_unique` (`application_id`),
  UNIQUE KEY `admissions_admission_number_unique` (`admission_number`),
  KEY `admissions_student_profile_id_foreign` (`student_profile_id`),
  KEY `admissions_faculty_id_foreign` (`faculty_id`),
  KEY `admissions_program_id_foreign` (`program_id`),
  KEY `admissions_issued_by_foreign` (`issued_by`),
  CONSTRAINT `admissions_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `admissions_faculty_id_foreign` FOREIGN KEY (`faculty_id`) REFERENCES `faculties` (`id`) ON DELETE SET NULL,
  CONSTRAINT `admissions_issued_by_foreign` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `admissions_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `admissions_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `application_countries`;

CREATE TABLE `application_countries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(3) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `application_countries_name_unique` (`name`),
  UNIQUE KEY `application_countries_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `application_documents`;

CREATE TABLE `application_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `application_id` bigint(20) unsigned NOT NULL,
  `student_profile_id` bigint(20) unsigned DEFAULT NULL,
  `document_name` varchar(255) NOT NULL,
  `document_type` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `storage_disk` varchar(255) NOT NULL DEFAULT 'local',
  `stored_filename` varchar(255) DEFAULT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `size` bigint(20) unsigned DEFAULT NULL,
  `current_version` int(10) unsigned NOT NULL DEFAULT 1,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `review_status` varchar(255) NOT NULL DEFAULT 'UPLOADED',
  `note` text DEFAULT NULL,
  `student_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `reviewer_id` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `internal_admin_notes` text DEFAULT NULL,
  `previous_document_id` bigint(20) unsigned DEFAULT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `app_docs_application_type_status_idx` (`application_id`,`document_type`,`status`),
  KEY `application_documents_student_profile_id_foreign` (`student_profile_id`),
  KEY `application_documents_reviewer_id_foreign` (`reviewer_id`),
  KEY `application_documents_previous_document_id_foreign` (`previous_document_id`),
  CONSTRAINT `application_documents_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `application_documents_previous_document_id_foreign` FOREIGN KEY (`previous_document_id`) REFERENCES `application_documents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `application_documents_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `application_documents_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `application_equivalencies`;

CREATE TABLE `application_equivalencies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `application_id` bigint(20) unsigned NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'WAITING_DOCUMENTS',
  `previous_university` varchar(255) DEFAULT NULL,
  `previous_country` varchar(255) DEFAULT NULL,
  `previous_program` varchar(255) DEFAULT NULL,
  `previous_study_language` varchar(255) DEFAULT NULL,
  `previous_education_type` varchar(255) DEFAULT NULL,
  `completed_years` int(10) unsigned DEFAULT NULL,
  `completed_semesters` int(10) unsigned DEFAULT NULL,
  `completed_credits` decimal(8,2) DEFAULT NULL,
  `accepted_credits` decimal(8,2) DEFAULT NULL,
  `rejected_credits` decimal(8,2) DEFAULT NULL,
  `proposed_entry_year` varchar(255) DEFAULT NULL,
  `proposed_entry_semester` varchar(255) DEFAULT NULL,
  `estimated_remaining_duration` varchar(255) DEFAULT NULL,
  `general_academic_notes` text DEFAULT NULL,
  `student_review_reason` text DEFAULT NULL,
  `reviewer_id` bigint(20) unsigned DEFAULT NULL,
  `result_issued_at` timestamp NULL DEFAULT NULL,
  `student_responded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `application_equivalencies_application_id_unique` (`application_id`),
  KEY `application_equivalencies_reviewer_id_foreign` (`reviewer_id`),
  CONSTRAINT `application_equivalencies_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `application_equivalencies_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `application_fee_payments`;

CREATE TABLE `application_fee_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `application_id` bigint(20) unsigned NOT NULL,
  `payment_number` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 50.00,
  `currency` varchar(3) NOT NULL DEFAULT 'USD',
  `status` varchar(255) NOT NULL DEFAULT 'NOT_PAID',
  `receipt_path` varchar(255) DEFAULT NULL,
  `receipt_original_name` varchar(255) DEFAULT NULL,
  `receipt_mime_type` varchar(255) DEFAULT NULL,
  `receipt_size` bigint(20) unsigned DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `reviewer_id` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `internal_admin_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `application_fee_payments_payment_number_unique` (`payment_number`),
  KEY `application_fee_payments_application_id_foreign` (`application_id`),
  KEY `application_fee_payments_reviewer_id_foreign` (`reviewer_id`),
  CONSTRAINT `application_fee_payments_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `application_fee_payments_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `application_nationalities`;

CREATE TABLE `application_nationalities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `country_name` varchar(255) DEFAULT NULL,
  `code` varchar(3) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `application_nationalities_name_unique` (`name`),
  UNIQUE KEY `application_nationalities_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `application_status_histories`;

CREATE TABLE `application_status_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `application_id` bigint(20) unsigned NOT NULL,
  `old_status` varchar(255) DEFAULT NULL,
  `new_status` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `comment` text DEFAULT NULL,
  `note` text DEFAULT NULL,
  `changed_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `application_status_histories_changed_by_foreign` (`changed_by`),
  KEY `app_status_application_date_idx` (`application_id`,`created_at`),
  CONSTRAINT `application_status_histories_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `application_status_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `contracts`;

CREATE TABLE `contracts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `application_id` bigint(20) unsigned NOT NULL,
  `contract_number` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `advance_percentage` tinyint(3) unsigned NOT NULL DEFAULT 30,
  `advance_amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `document_path` varchar(255) DEFAULT NULL,
  `issued_by` bigint(20) unsigned DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contracts_contract_number_unique` (`contract_number`),
  KEY `contracts_application_status_idx` (`application_id`,`status`),
  KEY `contracts_issued_by_foreign` (`issued_by`),
  CONSTRAINT `contracts_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `contracts_issued_by_foreign` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `document_requests`;

CREATE TABLE `document_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_profile_id` bigint(20) unsigned NOT NULL,
  `request_type` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `doc_requests_student_status_idx` (`student_profile_id`,`status`),
  CONSTRAINT `document_requests_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `document_requirements`;

CREATE TABLE `document_requirements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `application_id` bigint(20) unsigned DEFAULT NULL,
  `program_id` bigint(20) unsigned DEFAULT NULL,
  `requested_by` bigint(20) unsigned DEFAULT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `document_requirements_application_id_foreign` (`application_id`),
  KEY `document_requirements_program_id_foreign` (`program_id`),
  KEY `document_requirements_requested_by_foreign` (`requested_by`),
  CONSTRAINT `document_requirements_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `document_requirements_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `document_requirements_requested_by_foreign` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `education_backgrounds`;

CREATE TABLE `education_backgrounds` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_profile_id` bigint(20) unsigned NOT NULL,
  `institution_name` varchar(255) NOT NULL,
  `degree_obtained` varchar(255) NOT NULL,
  `gpa` varchar(255) NOT NULL,
  `graduation_year` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `education_backgrounds_student_profile_id_foreign` (`student_profile_id`),
  CONSTRAINT `education_backgrounds_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `enrollments`;

CREATE TABLE `enrollments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `application_id` bigint(20) unsigned DEFAULT NULL,
  `admission_id` bigint(20) unsigned DEFAULT NULL,
  `student_profile_id` bigint(20) unsigned NOT NULL,
  `program_id` bigint(20) unsigned NOT NULL,
  `student_number` varchar(255) NOT NULL,
  `academic_year` varchar(255) NOT NULL,
  `issue_date` date DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `document_path` varchar(255) DEFAULT NULL,
  `issued_by` bigint(20) unsigned DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `enrollments_student_number_unique` (`student_number`),
  UNIQUE KEY `enrollments_application_id_unique` (`application_id`),
  KEY `enrollments_student_profile_id_foreign` (`student_profile_id`),
  KEY `enrollments_program_id_foreign` (`program_id`),
  KEY `enrollments_admission_id_foreign` (`admission_id`),
  KEY `enrollments_issued_by_foreign` (`issued_by`),
  CONSTRAINT `enrollments_admission_id_foreign` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `enrollments_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `enrollments_issued_by_foreign` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `enrollments_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `enrollments_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `equivalency_courses`;

CREATE TABLE `equivalency_courses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `application_equivalency_id` bigint(20) unsigned NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `equivalency_courses_application_equivalency_id_foreign` (`application_equivalency_id`),
  CONSTRAINT `equivalency_courses_application_equivalency_id_foreign` FOREIGN KEY (`application_equivalency_id`) REFERENCES `application_equivalencies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `guardians`;

CREATE TABLE `guardians` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_profile_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `relation` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `guardians_student_profile_id_foreign` (`student_profile_id`),
  CONSTRAINT `guardians_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `housing_requests`;

CREATE TABLE `housing_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `application_id` bigint(20) unsigned NOT NULL,
  `student_profile_id` bigint(20) unsigned NOT NULL,
  `requested` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'NOT_REQUESTED',
  `preferred_room_type` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `reviewer_id` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `housing_requests_application_id_unique` (`application_id`),
  KEY `housing_requests_student_profile_id_foreign` (`student_profile_id`),
  KEY `housing_requests_reviewer_id_foreign` (`reviewer_id`),
  CONSTRAINT `housing_requests_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `housing_requests_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `housing_requests_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `payments`;

CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `contract_id` bigint(20) unsigned NOT NULL,
  `payment_number` varchar(255) NOT NULL,
  `payment_type` varchar(255) NOT NULL DEFAULT 'contract_advance',
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `receipt_path` varchar(255) DEFAULT NULL,
  `receipt_original_name` varchar(255) DEFAULT NULL,
  `receipt_mime_type` varchar(255) DEFAULT NULL,
  `receipt_size` bigint(20) unsigned DEFAULT NULL,
  `reviewer_id` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_payment_number_unique` (`payment_number`),
  KEY `payments_contract_status_date_idx` (`contract_id`,`status`,`payment_date`),
  KEY `payments_reviewer_id_foreign` (`reviewer_id`),
  CONSTRAINT `payments_contract_id_foreign` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payments_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `prikazes`;

CREATE TABLE `prikazes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `application_id` bigint(20) unsigned NOT NULL,
  `enrollment_id` bigint(20) unsigned DEFAULT NULL,
  `student_profile_id` bigint(20) unsigned NOT NULL,
  `program_id` bigint(20) unsigned DEFAULT NULL,
  `prikaz_number` varchar(255) NOT NULL,
  `issue_date` date DEFAULT NULL,
  `academic_year` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'ISSUED',
  `document_path` varchar(255) DEFAULT NULL,
  `issued_by` bigint(20) unsigned DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prikazes_application_id_unique` (`application_id`),
  UNIQUE KEY `prikazes_prikaz_number_unique` (`prikaz_number`),
  KEY `prikazes_enrollment_id_foreign` (`enrollment_id`),
  KEY `prikazes_student_profile_id_foreign` (`student_profile_id`),
  KEY `prikazes_program_id_foreign` (`program_id`),
  KEY `prikazes_issued_by_foreign` (`issued_by`),
  CONSTRAINT `prikazes_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `prikazes_enrollment_id_foreign` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `prikazes_issued_by_foreign` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `prikazes_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `prikazes_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `residence_permit_processes`;

CREATE TABLE `residence_permit_processes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `application_id` bigint(20) unsigned NOT NULL,
  `student_profile_id` bigint(20) unsigned NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'NOT_STARTED',
  `notes` text DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `reviewer_id` bigint(20) unsigned DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT NULL,
  `expires_at` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `residence_permit_processes_application_id_unique` (`application_id`),
  KEY `residence_permit_processes_student_profile_id_foreign` (`student_profile_id`),
  KEY `residence_permit_processes_reviewer_id_foreign` (`reviewer_id`),
  CONSTRAINT `residence_permit_processes_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `residence_permit_processes_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `residence_permit_processes_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `service_fee_payments`;

CREATE TABLE `service_fee_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `application_id` bigint(20) unsigned NOT NULL,
  `payment_number` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 300.00,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `status` varchar(255) NOT NULL DEFAULT 'UPLOADED',
  `receipt_path` varchar(255) DEFAULT NULL,
  `receipt_original_name` varchar(255) DEFAULT NULL,
  `receipt_mime_type` varchar(255) DEFAULT NULL,
  `receipt_size` bigint(20) unsigned DEFAULT NULL,
  `reviewer_id` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_fee_payments_payment_number_unique` (`payment_number`),
  KEY `service_fee_payments_application_id_foreign` (`application_id`),
  KEY `service_fee_payments_reviewer_id_foreign` (`reviewer_id`),
  CONSTRAINT `service_fee_payments_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `service_fee_payments_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `student_visa_processes`;

CREATE TABLE `student_visa_processes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `application_id` bigint(20) unsigned NOT NULL,
  `student_profile_id` bigint(20) unsigned NOT NULL,
  `telex_number` varchar(255) DEFAULT NULL,
  `telex_status` varchar(255) NOT NULL DEFAULT 'NOT_STARTED',
  `visa_status` varchar(255) NOT NULL DEFAULT 'NOT_STARTED',
  `visa_notes` text DEFAULT NULL,
  `reviewer_id` bigint(20) unsigned DEFAULT NULL,
  `telex_issued_at` timestamp NULL DEFAULT NULL,
  `visa_updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `student_visa_processes_application_id_unique` (`application_id`),
  KEY `student_visa_processes_student_profile_id_foreign` (`student_profile_id`),
  KEY `student_visa_processes_reviewer_id_foreign` (`reviewer_id`),
  CONSTRAINT `student_visa_processes_application_id_foreign` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_visa_processes_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `student_visa_processes_student_profile_id_foreign` FOREIGN KEY (`student_profile_id`) REFERENCES `student_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;

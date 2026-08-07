-- Public CMS tables generated from bstu_international

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `about_pages`;

CREATE TABLE `about_pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL DEFAULT 'main',
  `hero_contact_url` varchar(255) DEFAULT NULL,
  `hero_campus_url` varchar(255) DEFAULT NULL,
  `identity_image` varchar(255) DEFAULT NULL,
  `rector_profile_slug` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `about_pages_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `about_page_content_entries`;

CREATE TABLE `about_page_content_entries` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `about_page_id` bigint(20) unsigned NOT NULL,
  `path` varchar(255) NOT NULL,
  `value_type` varchar(30) NOT NULL DEFAULT 'text',
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `about_page_content_entries_about_page_id_path_unique` (`about_page_id`,`path`),
  CONSTRAINT `about_page_content_entries_about_page_id_foreign` FOREIGN KEY (`about_page_id`) REFERENCES `about_pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=191 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `about_page_content_entry_translations`;

CREATE TABLE `about_page_content_entry_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `about_page_content_entry_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(5) NOT NULL,
  `value` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `about_content_entry_locale_unique` (`about_page_content_entry_id`,`locale`),
  CONSTRAINT `about_content_entry_fk` FOREIGN KEY (`about_page_content_entry_id`) REFERENCES `about_page_content_entries` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=761 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `about_page_translations`;

CREATE TABLE `about_page_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `about_page_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(5) NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`content`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `about_page_translations_about_page_id_locale_unique` (`about_page_id`,`locale`),
  CONSTRAINT `about_page_translations_about_page_id_foreign` FOREIGN KEY (`about_page_id`) REFERENCES `about_pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `administration_profiles`;

CREATE TABLE `administration_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `telegram_url` varchar(255) DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_rector` tinyint(1) NOT NULL DEFAULT 0,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `administration_profiles_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `administration_profile_translations`;

CREATE TABLE `administration_profile_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `administration_profile_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(5) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `degree` varchar(255) DEFAULT NULL,
  `office_hours` varchar(255) DEFAULT NULL,
  `about` text DEFAULT NULL,
  `details` longtext DEFAULT NULL,
  `achievements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`achievements`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_profile_locale_unique` (`administration_profile_id`,`locale`),
  CONSTRAINT `admin_profile_translation_fk` FOREIGN KEY (`administration_profile_id`) REFERENCES `administration_profiles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `administration_settings`;

CREATE TABLE `administration_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL DEFAULT 'main',
  `home_limit` int(10) unsigned NOT NULL DEFAULT 6,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `administration_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `administration_setting_translations`;

CREATE TABLE `administration_setting_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `administration_setting_id` bigint(20) unsigned NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admin_setting_locale_unique` (`administration_setting_id`,`locale`),
  CONSTRAINT `admin_setting_translation_fk` FOREIGN KEY (`administration_setting_id`) REFERENCES `administration_settings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `announcements`;

CREATE TABLE `announcements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `priority` varchar(255) NOT NULL DEFAULT 'normal',
  `image` varchar(255) DEFAULT NULL,
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `views_count` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `announcements_slug_unique` (`slug`),
  KEY `announcements_public_order_idx` (`is_published`,`priority`,`created_at`),
  KEY `announcements_active_idx` (`is_published`,`ends_at`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `announcement_settings`;

CREATE TABLE `announcement_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL DEFAULT 'main',
  `home_limit` tinyint(3) unsigned NOT NULL DEFAULT 4,
  `recent_limit` tinyint(3) unsigned NOT NULL DEFAULT 5,
  `important_limit` tinyint(3) unsigned NOT NULL DEFAULT 3,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `announcement_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `announcement_setting_translations`;

CREATE TABLE `announcement_setting_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `announcement_setting_id` bigint(20) unsigned NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ann_setting_locale_unique` (`announcement_setting_id`,`locale`),
  CONSTRAINT `ann_setting_trans_setting_fk` FOREIGN KEY (`announcement_setting_id`) REFERENCES `announcement_settings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `announcement_translations`;

CREATE TABLE `announcement_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `announcement_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(255) NOT NULL,
  `category_label` varchar(255) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `announcement_translations_announcement_id_locale_unique` (`announcement_id`,`locale`),
  CONSTRAINT `announcement_translations_announcement_id_foreign` FOREIGN KEY (`announcement_id`) REFERENCES `announcements` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `auth_email_templates`;

CREATE TABLE `auth_email_templates` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `template_key` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `auth_email_templates_template_key_unique` (`template_key`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `auth_email_template_translations`;

CREATE TABLE `auth_email_template_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `auth_email_template_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(10) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `brand_name` varchar(255) DEFAULT NULL,
  `greeting` varchar(255) DEFAULT NULL,
  `intro` text DEFAULT NULL,
  `action_label` varchar(255) DEFAULT NULL,
  `expiry_notice` varchar(255) DEFAULT NULL,
  `no_action_notice` text DEFAULT NULL,
  `salutation` varchar(255) DEFAULT NULL,
  `signature` varchar(255) DEFAULT NULL,
  `subcopy` text DEFAULT NULL,
  `footer` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `auth_email_template_translations_auth_email_template_id_foreign` (`auth_email_template_id`),
  CONSTRAINT `auth_email_template_translations_auth_email_template_id_foreign` FOREIGN KEY (`auth_email_template_id`) REFERENCES `auth_email_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `auth_pages`;

CREATE TABLE `auth_pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `page_key` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `auth_pages_page_key_unique` (`page_key`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `auth_page_translations`;

CREATE TABLE `auth_page_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `auth_page_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(10) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` text DEFAULT NULL,
  `email_label` varchar(255) DEFAULT NULL,
  `email_placeholder` varchar(255) DEFAULT NULL,
  `password_label` varchar(255) DEFAULT NULL,
  `password_placeholder` varchar(255) DEFAULT NULL,
  `confirm_password_label` varchar(255) DEFAULT NULL,
  `confirm_password_placeholder` varchar(255) DEFAULT NULL,
  `submit_label` varchar(255) DEFAULT NULL,
  `loading_label` varchar(255) DEFAULT NULL,
  `forgot_password_label` varchar(255) DEFAULT NULL,
  `secondary_text` varchar(255) DEFAULT NULL,
  `secondary_action_label` varchar(255) DEFAULT NULL,
  `secondary_action_url` varchar(255) DEFAULT NULL,
  `success_title` varchar(255) DEFAULT NULL,
  `success_message` text DEFAULT NULL,
  `back_label` varchar(255) DEFAULT NULL,
  `show_password_label` varchar(255) DEFAULT NULL,
  `hide_password_label` varchar(255) DEFAULT NULL,
  `validation_required_message` varchar(255) DEFAULT NULL,
  `validation_mismatch_message` varchar(255) DEFAULT NULL,
  `error_message` varchar(255) DEFAULT NULL,
  `logo_alt` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `auth_page_translations_auth_page_id_locale_unique` (`auth_page_id`,`locale`),
  CONSTRAINT `auth_page_translations_auth_page_id_foreign` FOREIGN KEY (`auth_page_id`) REFERENCES `auth_pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `blogs`;

CREATE TABLE `blogs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `author_image` varchar(255) DEFAULT NULL,
  `category` varchar(255) NOT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `views_count` int(10) unsigned NOT NULL DEFAULT 0,
  `comments_count` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blogs_slug_unique` (`slug`),
  KEY `blogs_category_index` (`category`),
  KEY `blogs_published_at_index` (`published_at`),
  KEY `blogs_is_published_index` (`is_published`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `blog_settings`;

CREATE TABLE `blog_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `home_limit` tinyint(3) unsigned NOT NULL DEFAULT 3,
  `recent_limit` tinyint(3) unsigned NOT NULL DEFAULT 5,
  `home_icon` varchar(255) NOT NULL DEFAULT 'book-open',
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blog_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `blog_setting_translations`;

CREATE TABLE `blog_setting_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `blog_setting_id` bigint(20) unsigned NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blog_setting_translations_blog_setting_id_locale_unique` (`blog_setting_id`,`locale`),
  KEY `blog_setting_translations_locale_index` (`locale`),
  CONSTRAINT `blog_setting_translations_blog_setting_id_foreign` FOREIGN KEY (`blog_setting_id`) REFERENCES `blog_settings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `blog_translations`;

CREATE TABLE `blog_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(5) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `category_label` varchar(255) DEFAULT NULL,
  `summary` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blog_translations_blog_id_locale_unique` (`blog_id`,`locale`),
  KEY `blog_translations_locale_index` (`locale`),
  CONSTRAINT `blog_translations_blog_id_foreign` FOREIGN KEY (`blog_id`) REFERENCES `blogs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `contact_pages`;

CREATE TABLE `contact_pages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL DEFAULT 'main',
  `map_embed_url` text DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contact_pages_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `contact_page_translations`;

CREATE TABLE `contact_page_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `contact_page_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(5) NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`content`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `contact_page_translations_contact_page_id_locale_unique` (`contact_page_id`,`locale`),
  CONSTRAINT `contact_page_translations_contact_page_id_foreign` FOREIGN KEY (`contact_page_id`) REFERENCES `contact_pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `green_campus_articles`;

CREATE TABLE `green_campus_articles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `gallery` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`gallery`)),
  `views` int(11) NOT NULL DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `green_campus_articles_slug_unique` (`slug`),
  KEY `green_articles_created_idx` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `green_campus_article_translations`;

CREATE TABLE `green_campus_article_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `green_campus_article_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `excerpt` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `author` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gc_art_id_locale_unique` (`green_campus_article_id`,`locale`),
  CONSTRAINT `gc_art_foreign` FOREIGN KEY (`green_campus_article_id`) REFERENCES `green_campus_articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `green_campus_settings`;

CREATE TABLE `green_campus_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL DEFAULT 'main',
  `home_limit` int(11) NOT NULL DEFAULT 3,
  `recent_limit` int(11) NOT NULL DEFAULT 4,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `green_campus_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `green_campus_setting_translations`;

CREATE TABLE `green_campus_setting_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `green_campus_setting_id` bigint(20) unsigned NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gc_setting_locale_unique` (`green_campus_setting_id`,`locale`),
  CONSTRAINT `gc_setting_foreign` FOREIGN KEY (`green_campus_setting_id`) REFERENCES `green_campus_settings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `green_campus_stats`;

CREATE TABLE `green_campus_stats` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `icon` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `green_stats_sort_idx` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `green_campus_stat_translations`;

CREATE TABLE `green_campus_stat_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `green_campus_stat_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gc_stat_id_locale_unique` (`green_campus_stat_id`,`locale`),
  CONSTRAINT `gc_stat_foreign` FOREIGN KEY (`green_campus_stat_id`) REFERENCES `green_campus_stats` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `home_sections`;

CREATE TABLE `home_sections` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `section_key` varchar(255) NOT NULL,
  `section_type` varchar(255) NOT NULL DEFAULT 'content',
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `home_sections_section_key_unique` (`section_key`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `home_section_items`;

CREATE TABLE `home_section_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `home_section_id` bigint(20) unsigned NOT NULL,
  `item_key` varchar(255) DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  `suffix` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`settings`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `home_section_items_home_section_id_sort_order_index` (`home_section_id`,`sort_order`),
  CONSTRAINT `home_section_items_home_section_id_foreign` FOREIGN KEY (`home_section_id`) REFERENCES `home_sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `home_section_item_translations`;

CREATE TABLE `home_section_item_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `home_section_item_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(10) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  `action_label` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `home_section_item_locale_unique` (`home_section_item_id`,`locale`),
  CONSTRAINT `home_section_item_translations_home_section_item_id_foreign` FOREIGN KEY (`home_section_item_id`) REFERENCES `home_section_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=136 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `home_section_translations`;

CREATE TABLE `home_section_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `home_section_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(10) NOT NULL,
  `eyebrow` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `secondary_title` varchar(255) DEFAULT NULL,
  `secondary_description` text DEFAULT NULL,
  `cta_label` varchar(255) DEFAULT NULL,
  `cta_url` varchar(255) DEFAULT NULL,
  `image_alt` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `home_section_locale_unique` (`home_section_id`,`locale`),
  CONSTRAINT `home_section_translations_home_section_id_foreign` FOREIGN KEY (`home_section_id`) REFERENCES `home_sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `interactive_service_settings`;

CREATE TABLE `interactive_service_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL DEFAULT 'main',
  `home_limit` int(11) NOT NULL DEFAULT 4,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `interactive_service_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `interactive_service_setting_translations`;

CREATE TABLE `interactive_service_setting_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `interactive_service_setting_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(255) NOT NULL,
  `home_tag` varchar(255) DEFAULT NULL,
  `home_title` varchar(255) DEFAULT NULL,
  `view_all_label` varchar(255) DEFAULT NULL,
  `loading_label` varchar(255) DEFAULT NULL,
  `no_results_label` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `interactive_service_setting_locale_unique` (`interactive_service_setting_id`,`locale`),
  CONSTRAINT `int_service_setting_tr_setting_fk` FOREIGN KEY (`interactive_service_setting_id`) REFERENCES `interactive_service_settings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `news`;

CREATE TABLE `news` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(255) NOT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `views_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `news_slug_unique` (`slug`),
  KEY `news_published_date_idx` (`is_published`,`published_at`),
  KEY `news_category_published_date_idx` (`category`,`is_published`,`published_at`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `news_event_settings`;

CREATE TABLE `news_event_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `home_limit` tinyint(3) unsigned NOT NULL DEFAULT 4,
  `recent_limit` tinyint(3) unsigned NOT NULL DEFAULT 5,
  `home_icon` varchar(255) NOT NULL DEFAULT 'newspaper',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `news_event_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `news_event_setting_translations`;

CREATE TABLE `news_event_setting_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `news_event_setting_id` bigint(20) unsigned NOT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `news_event_settings_locale_unique` (`news_event_setting_id`,`locale`),
  CONSTRAINT `news_event_setting_translations_news_event_setting_id_foreign` FOREIGN KEY (`news_event_setting_id`) REFERENCES `news_event_settings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `news_translations`;

CREATE TABLE `news_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `news_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `news_translations_news_id_locale_unique` (`news_id`,`locale`),
  CONSTRAINT `news_translations_news_id_foreign` FOREIGN KEY (`news_id`) REFERENCES `news` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=245 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `services`;

CREATE TABLE `services` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `services_slug_unique` (`slug`),
  KEY `services_active_sort_idx` (`is_active`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `service_translations`;

CREATE TABLE `service_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `service_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `action_label` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_translations_service_id_locale_unique` (`service_id`,`locale`),
  CONSTRAINT `service_translations_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `videos`;

CREATE TABLE `videos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `video_type` varchar(255) NOT NULL DEFAULT 'youtube',
  `youtube_id` varchar(255) DEFAULT NULL,
  `duration` varchar(255) DEFAULT NULL,
  `views_count` int(10) unsigned NOT NULL DEFAULT 0,
  `likes_count` int(10) unsigned NOT NULL DEFAULT 0,
  `published_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `videos_slug_unique` (`slug`),
  KEY `videos_active_sort_idx` (`is_active`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `video_gallery_settings`;

CREATE TABLE `video_gallery_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `home_limit` smallint(5) unsigned NOT NULL DEFAULT 4,
  `subscriber_count` int(10) unsigned NOT NULL DEFAULT 0,
  `youtube_channel_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `video_gallery_settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `video_gallery_setting_translations`;

CREATE TABLE `video_gallery_setting_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `video_gallery_setting_id` bigint(20) unsigned NOT NULL,
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
  `verified_channel_label` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `video_gallery_setting_locale_unique` (`video_gallery_setting_id`,`locale`),
  CONSTRAINT `vgs_trans_setting_fk` FOREIGN KEY (`video_gallery_setting_id`) REFERENCES `video_gallery_settings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `video_translations`;

CREATE TABLE `video_translations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `video_id` bigint(20) unsigned NOT NULL,
  `locale` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `video_translations_video_id_locale_unique` (`video_id`,`locale`),
  CONSTRAINT `video_translations_video_id_foreign` FOREIGN KEY (`video_id`) REFERENCES `videos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=125 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;

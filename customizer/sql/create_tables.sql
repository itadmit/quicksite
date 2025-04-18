-- Create landing_pages table if not exists
CREATE TABLE IF NOT EXISTS `landing_pages` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `slug` varchar(255) NOT NULL,
    `description` text,
    `status` varchar(20) NOT NULL DEFAULT 'draft',
    `template_id` bigint(20) UNSIGNED DEFAULT NULL,
    `user_id` bigint(20) UNSIGNED NOT NULL,
    `is_mobile_friendly` tinyint(1) NOT NULL DEFAULT '1',
    `custom_domain_id` bigint(20) UNSIGNED DEFAULT NULL,
    `seo_title` varchar(255) DEFAULT NULL,
    `seo_description` text,
    `seo_keywords` text,
    `published_at` datetime DEFAULT NULL,
    `created_at` datetime NOT NULL,
    `updated_at` datetime NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `slug` (`slug`),
    KEY `template_id` (`template_id`),
    KEY `user_id` (`user_id`),
    KEY `custom_domain_id` (`custom_domain_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create landing_page_contents table if not exists
CREATE TABLE IF NOT EXISTS `landing_page_contents` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `landing_page_id` bigint(20) UNSIGNED NOT NULL,
    `content` longtext NOT NULL,
    `version` int(11) NOT NULL DEFAULT '1',
    `is_current` tinyint(1) NOT NULL DEFAULT '1',
    `created_at` datetime NOT NULL,
    `updated_at` datetime NOT NULL,
    PRIMARY KEY (`id`),
    KEY `landing_page_id` (`landing_page_id`),
    KEY `is_current` (`is_current`),
    CONSTRAINT `fk_landing_page_contents_landing_page` FOREIGN KEY (`landing_page_id`) REFERENCES `landing_pages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create templates table if not exists
CREATE TABLE IF NOT EXISTS `templates` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `type` varchar(50) NOT NULL,
    `category_id` bigint(20) UNSIGNED DEFAULT NULL,
    `thumbnail` varchar(255) DEFAULT NULL,
    `html_content` longtext,
    `css_content` longtext,
    `js_content` longtext,
    `plan_level` int(11) NOT NULL DEFAULT '0',
    `is_premium` tinyint(1) NOT NULL DEFAULT '0',
    `created_at` datetime NOT NULL,
    `updated_at` datetime NOT NULL,
    PRIMARY KEY (`id`),
    KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 
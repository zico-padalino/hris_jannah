-- Tabel branding aplikasi (nama & logo)
-- Jalankan standalone di MySQL

CREATE TABLE IF NOT EXISTS `app_branding` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `app_name` VARCHAR(100) NOT NULL DEFAULT '',
    `logo_path` VARCHAR(255) NULL DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `app_branding` (`id`, `app_name`, `logo_path`, `created_at`, `updated_at`)
SELECT 1, '', NULL, NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `app_branding` WHERE `id` = 1
);

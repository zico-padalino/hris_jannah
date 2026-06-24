-- Tabel pengumuman + modul aksi CRUD
-- Jalankan setelah migration atau standalone di MySQL

CREATE TABLE IF NOT EXISTS `announcements` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `branch_id` BIGINT UNSIGNED NULL DEFAULT NULL,
    `created_by` BIGINT UNSIGNED NULL DEFAULT NULL,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `starts_at` DATE NOT NULL,
    `ends_at` DATE NOT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `announcements_branch_id_foreign` (`branch_id`),
    KEY `announcements_created_by_foreign` (`created_by`),
    KEY `announcements_period_index` (`starts_at`, `ends_at`, `is_active`),
    CONSTRAINT `announcements_branch_id_foreign`
        FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
    CONSTRAINT `announcements_created_by_foreign`
        FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Modul tombol aksi (jika belum ada)
INSERT INTO `action_modules` (`module_key`, `label`, `sort_order`, `created_at`, `updated_at`)
SELECT 'announcements', 'Pengumuman', 95, NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `action_modules` WHERE `module_key` = 'announcements'
);

INSERT INTO `module_actions` (`module_key`, `action_key`, `label`, `icon_type`, `sort_order`, `created_at`, `updated_at`)
SELECT 'announcements', 'create', 'Tambah', 'extra', 10, NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `module_actions` WHERE `module_key` = 'announcements' AND `action_key` = 'create'
);

INSERT INTO `module_actions` (`module_key`, `action_key`, `label`, `icon_type`, `sort_order`, `created_at`, `updated_at`)
SELECT 'announcements', 'edit', 'Ubah', 'edit', 20, NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `module_actions` WHERE `module_key` = 'announcements' AND `action_key` = 'edit'
);

INSERT INTO `module_actions` (`module_key`, `action_key`, `label`, `icon_type`, `sort_order`, `created_at`, `updated_at`)
SELECT 'announcements', 'delete', 'Hapus', 'delete', 30, NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM `module_actions` WHERE `module_key` = 'announcements' AND `action_key` = 'delete'
);

-- HR & Admin Cabang: semua tombol aktif
INSERT INTO `role_module_action_visibility` (`role`, `module_action_id`, `created_at`, `updated_at`)
SELECT 'hr', `id`, NOW(), NOW() FROM `module_actions` WHERE `module_key` = 'announcements'
AND NOT EXISTS (
    SELECT 1 FROM `role_module_action_visibility` rmav
    INNER JOIN `module_actions` ma ON ma.id = rmav.module_action_id
    WHERE rmav.role = 'hr' AND ma.module_key = 'announcements'
);

INSERT INTO `role_module_action_visibility` (`role`, `module_action_id`, `created_at`, `updated_at`)
SELECT 'branch_admin', `id`, NOW(), NOW() FROM `module_actions` WHERE `module_key` = 'announcements'
AND NOT EXISTS (
    SELECT 1 FROM `role_module_action_visibility` rmav
    INNER JOIN `module_actions` ma ON ma.id = rmav.module_action_id
    WHERE rmav.role = 'branch_admin' AND ma.module_key = 'announcements'
);

-- =============================================================================
-- STRUKTUR MENU SIDEBAR — RESET LENGKAP
-- Database: MySQL / MariaDB
-- Jalankan setelah backup. Urutan: HAPUS → BUAT TABEL → ISI DATA
-- Setelah selesai: php artisan cache:clear
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =============================================================================
-- BAGIAN 1: HAPUS DATA LAMA
-- =============================================================================

-- 1.1 Hapus konfigurasi sidebar dari system_settings (format JSON lama)
DELETE FROM `system_settings`
WHERE `key` IN (
    'sidebar_groups',
    'sidebar_visibility',
    'sidebar_custom_modules',
    'sidebar_position',
    'sidebar_order'
);

-- 1.2 Hapus tabel sidebar (jika sudah ada)
DROP TABLE IF EXISTS `sidebar_menu_visibility`;
DROP TABLE IF EXISTS `sidebar_menu_items`;
DROP TABLE IF EXISTS `sidebar_custom_modules`;
DROP TABLE IF EXISTS `sidebar_menu_groups`;
DROP TABLE IF EXISTS `sidebar_layouts`;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- BAGIAN 2: BUAT TABEL BARU
-- =============================================================================

CREATE TABLE `sidebar_layouts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `position` VARCHAR(10) NOT NULL DEFAULT 'left',
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sidebar_menu_groups` (
    `id` VARCHAR(64) NOT NULL,
    `builtin` VARCHAR(64) DEFAULT NULL,
    `label` VARCHAR(80) DEFAULT NULL,
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sidebar_menu_items` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `group_id` VARCHAR(64) NOT NULL,
    `item_key` VARCHAR(64) NOT NULL,
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `sidebar_menu_items_group_id_item_key_unique` (`group_id`, `item_key`),
    KEY `sidebar_menu_items_item_key_index` (`item_key`),
    CONSTRAINT `sidebar_menu_items_group_id_foreign`
        FOREIGN KEY (`group_id`) REFERENCES `sidebar_menu_groups` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sidebar_custom_modules` (
    `key` VARCHAR(64) NOT NULL,
    `label` VARCHAR(80) NOT NULL,
    `url` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sidebar_menu_visibility` (
    `item_key` VARCHAR(64) NOT NULL,
    `role` VARCHAR(64) NOT NULL,
    `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`item_key`, `role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- BAGIAN 3: TAMBAH DATA BARU
-- =============================================================================

-- 3.1 Posisi sidebar
INSERT INTO `sidebar_layouts` (`id`, `position`, `created_at`, `updated_at`) VALUES
(1, 'left', NOW(), NOW());

-- 3.2 Grup menu (urutan tampil)
INSERT INTO `sidebar_menu_groups` (`id`, `builtin`, `label`, `sort_order`, `created_at`, `updated_at`) VALUES
('grp_dashboard',          'dashboard',          NULL, 0, NOW(), NOW()),
('grp_section_attendance', 'section_attendance', NULL, 1, NOW(), NOW()),
('grp_section_leave',      'section_leave',      NULL, 2, NOW(), NOW()),
('grp_section_payroll',    'section_payroll',    NULL, 3, NOW(), NOW()),
('grp_section_master',     'section_master',     NULL, 4, NOW(), NOW()),
('grp_section_system',     'section_system',     NULL, 5, NOW(), NOW());

-- 3.3 Item menu per grup
INSERT INTO `sidebar_menu_items` (`group_id`, `item_key`, `sort_order`, `created_at`, `updated_at`) VALUES
-- Dashboard
('grp_dashboard', 'dashboard', 0, NOW(), NOW()),

-- Absensi
('grp_section_attendance', 'attendance_scan',       0, NOW(), NOW()),
('grp_section_attendance', 'attendance_history',    1, NOW(), NOW()),
('grp_section_attendance', 'attendance_manage',     2, NOW(), NOW()),
('grp_section_attendance', 'fingerprint_devices',   3, NOW(), NOW()),

-- Cuti
('grp_section_leave', 'leave_history',  0, NOW(), NOW()),
('grp_section_leave', 'leave_create',   1, NOW(), NOW()),
('grp_section_leave', 'leave_approval', 2, NOW(), NOW()),

-- Penggajian
('grp_section_payroll', 'payroll', 0, NOW(), NOW()),

-- Master Data (Kelola Data)
('grp_section_master', 'branches',         0, NOW(), NOW()),
('grp_section_master', 'departments',      1, NOW(), NOW()),
('grp_section_master', 'positions',        2, NOW(), NOW()),
('grp_section_master', 'employees',        3, NOW(), NOW()),
('grp_section_master', 'shift_templates',  4, NOW(), NOW()),
('grp_section_master', 'employee_shifts',  5, NOW(), NOW()),
('grp_section_master', 'holidays',         6, NOW(), NOW()),

-- Sistem
('grp_section_system', 'reports',  0, NOW(), NOW()),
('grp_section_system', 'users',    1, NOW(), NOW()),
('grp_section_system', 'roles',    2, NOW(), NOW()),
('grp_section_system', 'settings', 3, NOW(), NOW());

-- 3.4 Modul kustom (kosong default — tambahkan jika punya mod_xxx)
-- INSERT INTO `sidebar_custom_modules` (`key`, `label`, `url`, `created_at`, `updated_at`) VALUES
-- ('mod_contoh', 'Nama Modul', '/contoh', NOW(), NOW());

-- 3.5 Visibilitas menu per role (semua role bisa lihat semua menu secara default)
INSERT INTO `sidebar_menu_visibility` (`item_key`, `role`, `is_visible`, `created_at`, `updated_at`) VALUES
-- dashboard
('dashboard', 'super_admin', 1, NOW(), NOW()),
('dashboard', 'hr',          1, NOW(), NOW()),
('dashboard', 'branch_admin',1, NOW(), NOW()),
('dashboard', 'employee',    1, NOW(), NOW()),

-- absensi
('attendance_scan',       'super_admin', 1, NOW(), NOW()),
('attendance_scan',       'hr',          1, NOW(), NOW()),
('attendance_scan',       'branch_admin',1, NOW(), NOW()),
('attendance_scan',       'employee',    1, NOW(), NOW()),
('attendance_history',    'super_admin', 1, NOW(), NOW()),
('attendance_history',    'hr',          1, NOW(), NOW()),
('attendance_history',    'branch_admin',1, NOW(), NOW()),
('attendance_history',    'employee',    1, NOW(), NOW()),
('attendance_manage',     'super_admin', 1, NOW(), NOW()),
('attendance_manage',     'hr',          1, NOW(), NOW()),
('attendance_manage',     'branch_admin',1, NOW(), NOW()),
('attendance_manage',     'employee',    0, NOW(), NOW()),
('fingerprint_devices',   'super_admin', 1, NOW(), NOW()),
('fingerprint_devices',   'hr',          1, NOW(), NOW()),
('fingerprint_devices',   'branch_admin',1, NOW(), NOW()),
('fingerprint_devices',   'employee',    0, NOW(), NOW()),

-- cuti
('leave_history',  'super_admin', 1, NOW(), NOW()),
('leave_history',  'hr',          1, NOW(), NOW()),
('leave_history',  'branch_admin',1, NOW(), NOW()),
('leave_history',  'employee',    1, NOW(), NOW()),
('leave_create',   'super_admin', 1, NOW(), NOW()),
('leave_create',   'hr',          1, NOW(), NOW()),
('leave_create',   'branch_admin',1, NOW(), NOW()),
('leave_create',   'employee',    1, NOW(), NOW()),
('leave_approval', 'super_admin', 1, NOW(), NOW()),
('leave_approval', 'hr',          1, NOW(), NOW()),
('leave_approval', 'branch_admin',1, NOW(), NOW()),
('leave_approval', 'employee',    0, NOW(), NOW()),

-- penggajian
('payroll', 'super_admin', 1, NOW(), NOW()),
('payroll', 'hr',          1, NOW(), NOW()),
('payroll', 'branch_admin',1, NOW(), NOW()),
('payroll', 'employee',    1, NOW(), NOW()),

-- master data
('branches',         'super_admin', 1, NOW(), NOW()),
('branches',         'hr',          1, NOW(), NOW()),
('branches',         'branch_admin',0, NOW(), NOW()),
('branches',         'employee',    0, NOW(), NOW()),
('departments',      'super_admin', 1, NOW(), NOW()),
('departments',      'hr',          1, NOW(), NOW()),
('departments',      'branch_admin',1, NOW(), NOW()),
('departments',      'employee',    0, NOW(), NOW()),
('positions',        'super_admin', 1, NOW(), NOW()),
('positions',        'hr',          1, NOW(), NOW()),
('positions',        'branch_admin',1, NOW(), NOW()),
('positions',        'employee',    0, NOW(), NOW()),
('employees',        'super_admin', 1, NOW(), NOW()),
('employees',        'hr',          1, NOW(), NOW()),
('employees',        'branch_admin',1, NOW(), NOW()),
('employees',        'employee',    0, NOW(), NOW()),
('shift_templates',  'super_admin', 1, NOW(), NOW()),
('shift_templates',  'hr',          1, NOW(), NOW()),
('shift_templates',  'branch_admin',1, NOW(), NOW()),
('shift_templates',  'employee',    0, NOW(), NOW()),
('employee_shifts',  'super_admin', 1, NOW(), NOW()),
('employee_shifts',  'hr',          1, NOW(), NOW()),
('employee_shifts',  'branch_admin',1, NOW(), NOW()),
('employee_shifts',  'employee',    0, NOW(), NOW()),
('holidays',         'super_admin', 1, NOW(), NOW()),
('holidays',         'hr',          1, NOW(), NOW()),
('holidays',         'branch_admin',1, NOW(), NOW()),
('holidays',         'employee',    0, NOW(), NOW()),

-- sistem
('reports',  'super_admin', 1, NOW(), NOW()),
('reports',  'hr',          1, NOW(), NOW()),
('reports',  'branch_admin',1, NOW(), NOW()),
('reports',  'employee',    0, NOW(), NOW()),
('users',    'super_admin', 1, NOW(), NOW()),
('users',    'hr',          0, NOW(), NOW()),
('users',    'branch_admin',0, NOW(), NOW()),
('users',    'employee',    0, NOW(), NOW()),
('roles',    'super_admin', 1, NOW(), NOW()),
('roles',    'hr',          0, NOW(), NOW()),
('roles',    'branch_admin',0, NOW(), NOW()),
('roles',    'employee',    0, NOW(), NOW()),
('settings', 'super_admin', 1, NOW(), NOW()),
('settings', 'hr',          0, NOW(), NOW()),
('settings', 'branch_admin',0, NOW(), NOW()),
('settings', 'employee',    0, NOW(), NOW());

-- =============================================================================
-- SELESAI
-- =============================================================================

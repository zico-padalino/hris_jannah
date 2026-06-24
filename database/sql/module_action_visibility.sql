-- Modul & tombol aksi per halaman + visibilitas per role
-- Jalankan setelah migration atau standalone di MySQL

CREATE TABLE IF NOT EXISTS `action_modules` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `module_key` VARCHAR(64) NOT NULL,
    `label` VARCHAR(120) NOT NULL,
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `action_modules_module_key_unique` (`module_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `module_actions` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `module_key` VARCHAR(64) NOT NULL,
    `action_key` VARCHAR(64) NOT NULL,
    `label` VARCHAR(120) NOT NULL,
    `icon_type` VARCHAR(32) NOT NULL DEFAULT 'extra',
    `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `module_actions_module_action_unique` (`module_key`, `action_key`),
    CONSTRAINT `module_actions_module_key_foreign`
        FOREIGN KEY (`module_key`) REFERENCES `action_modules` (`module_key`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_module_action_visibility` (
    `role` VARCHAR(32) NOT NULL,
    `module_action_id` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`role`, `module_action_id`),
    CONSTRAINT `role_module_action_visibility_action_foreign`
        FOREIGN KEY (`module_action_id`) REFERENCES `module_actions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reset data modul bawaan (opsional)
DELETE FROM `role_module_action_visibility`;
DELETE FROM `module_actions`;
DELETE FROM `action_modules`;

INSERT INTO `action_modules` (`module_key`, `label`, `sort_order`, `created_at`, `updated_at`) VALUES
('branches',           'Cabang RS',              10, NOW(), NOW()),
('employees',          'Pegawai',                20, NOW(), NOW()),
('departments',        'Departemen',             30, NOW(), NOW()),
('positions',          'Jabatan',                40, NOW(), NOW()),
('holidays',           'Hari Libur',             50, NOW(), NOW()),
('users',              'Pengguna',               60, NOW(), NOW()),
('roles',              'Grup / Hak Akses',       70, NOW(), NOW()),
('fingerprint_devices','Mesin Fingerprint',      80, NOW(), NOW()),
('shift_templates',    'Jadwal Kerja',           90, NOW(), NOW());

INSERT INTO `module_actions` (`module_key`, `action_key`, `label`, `icon_type`, `sort_order`, `created_at`, `updated_at`) VALUES
('branches',            'create',   'Tambah',           'extra',    10, NOW(), NOW()),
('branches',            'view',     'Detail',           'view',     20, NOW(), NOW()),
('branches',            'edit',     'Ubah',             'edit',     30, NOW(), NOW()),
('branches',            'location', 'Tambah Lokasi',    'location', 40, NOW(), NOW()),
('branches',            'delete',   'Hapus',            'delete',   50, NOW(), NOW()),
('employees',           'create',   'Tambah',           'extra',    10, NOW(), NOW()),
('employees',           'view',     'Detail',           'view',     20, NOW(), NOW()),
('employees',           'edit',     'Ubah',             'edit',     30, NOW(), NOW()),
('employees',           'delete',   'Hapus',            'delete',   40, NOW(), NOW()),
('departments',         'create',   'Tambah',           'extra',    10, NOW(), NOW()),
('departments',         'edit',     'Ubah',             'edit',     20, NOW(), NOW()),
('departments',         'delete',   'Hapus',            'delete',   30, NOW(), NOW()),
('positions',           'create',   'Tambah',           'extra',    10, NOW(), NOW()),
('positions',           'edit',     'Ubah',             'edit',     20, NOW(), NOW()),
('positions',           'delete',   'Hapus',            'delete',   30, NOW(), NOW()),
('holidays',            'create',   'Tambah',           'extra',    10, NOW(), NOW()),
('holidays',            'edit',     'Ubah',             'edit',     20, NOW(), NOW()),
('holidays',            'delete',   'Hapus',            'delete',   30, NOW(), NOW()),
('users',               'create',   'Tambah',           'extra',    10, NOW(), NOW()),
('users',               'edit',     'Ubah',             'edit',     20, NOW(), NOW()),
('roles',               'edit',     'Ubah',             'edit',     10, NOW(), NOW()),
('roles',               'access_rights', 'Hak Akses',   'extra',    20, NOW(), NOW()),
('fingerprint_devices', 'edit',     'Kelola',           'edit',     10, NOW(), NOW()),
('shift_templates',     'create',   'Tambah',           'extra',    10, NOW(), NOW()),
('shift_templates',     'edit',     'Ubah',             'edit',     20, NOW(), NOW()),
('shift_templates',     'delete',   'Hapus',            'delete',   30, NOW(), NOW());

-- HR & Admin Cabang: semua tombol aktif
INSERT INTO `role_module_action_visibility` (`role`, `module_action_id`, `created_at`, `updated_at`)
SELECT 'hr', `id`, NOW(), NOW() FROM `module_actions`;

INSERT INTO `role_module_action_visibility` (`role`, `module_action_id`, `created_at`, `updated_at`)
SELECT 'branch_admin', `id`, NOW(), NOW() FROM `module_actions`;

-- Pegawai: hanya tombol Detail (view)
INSERT INTO `role_module_action_visibility` (`role`, `module_action_id`, `created_at`, `updated_at`)
SELECT 'employee', `id`, NOW(), NOW() FROM `module_actions` WHERE `action_key` = 'view';

-- Super Admin tidak perlu baris (selalu semua tombol tampil di aplikasi)

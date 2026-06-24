-- Pengaturan maintenance modul (disimpan di system_settings)
-- Jalankan setelah migration atau standalone di MySQL

INSERT INTO `system_settings` (`key`, `value`, `label`, `created_at`, `updated_at`) VALUES
('module_maintenance_modules', '[]', 'Modul dalam maintenance', NOW(), NOW()),
('module_maintenance_message', '', 'Pesan maintenance modul', NOW(), NOW())
ON DUPLICATE KEY UPDATE
    `label` = VALUES(`label`),
    `updated_at` = NOW();

-- Contoh: aktifkan maintenance untuk payroll dan laporan
-- UPDATE `system_settings` SET `value` = '["payroll","reports"]' WHERE `key` = 'module_maintenance_modules';
-- UPDATE `system_settings` SET `value` = 'Modul sedang diperbarui. Silakan coba lagi nanti.' WHERE `key` = 'module_maintenance_message';

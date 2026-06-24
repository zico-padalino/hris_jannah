-- Pengaturan password default pengguna
-- Jalankan setelah migration atau standalone di MySQL

INSERT INTO `system_settings` (`key`, `value`, `label`, `created_at`, `updated_at`) VALUES
('user_default_password_mode', 'employee_number', 'Mode password default pengguna', NOW(), NOW()),
('user_default_password_custom', '', 'Password default pengguna (kustom)', NOW(), NOW())
ON DUPLICATE KEY UPDATE
    `label` = VALUES(`label`),
    `updated_at` = NOW();

-- Contoh: gunakan password kustom
-- UPDATE `system_settings` SET `value` = 'custom' WHERE `key` = 'user_default_password_mode';
-- UPDATE `system_settings` SET `value` = 'rs123456' WHERE `key` = 'user_default_password_custom';

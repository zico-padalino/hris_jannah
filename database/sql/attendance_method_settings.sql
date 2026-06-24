-- Pengaturan metode absensi (Pengaturan Sistem → Metode Absensi)
-- Hapus data lama (opsional)
DELETE FROM `system_settings`
WHERE `key` IN (
    'attendance_method_fingerprint',
    'attendance_method_photo',
    'attendance_method_gps'
);

-- Tambah data baru
INSERT INTO `system_settings` (`key`, `value`, `label`, `created_at`, `updated_at`) VALUES
('attendance_method_fingerprint', '1', 'Metode absensi: fingerprint', NOW(), NOW()),
('attendance_method_photo',       '1', 'Metode absensi: foto/wajah', NOW(), NOW()),
('attendance_method_gps',         '0', 'Metode absensi: GPS lokasi', NOW(), NOW());

-- Set GPS = 1 jika mesin fingerprint rusak (cadangan lokasi saja):
-- UPDATE `system_settings` SET `value` = '1', `updated_at` = NOW() WHERE `key` = 'attendance_method_gps';

-- Opsi Non Shift untuk pegawai
-- Jalankan setelah migration atau standalone di MySQL

ALTER TABLE `employees`
    ADD COLUMN `is_non_shift` TINYINT(1) NOT NULL DEFAULT 0 AFTER `shift_id`;

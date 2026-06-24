-- =============================================================================
-- DATA JABATAN RUMAH SAKIT
-- Tabel: positions
-- Jalankan setelah backup. Setelah selesai tidak perlu cache:clear.
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =============================================================================
-- BAGIAN 1: HAPUS DATA JABATAN LAMA
-- =============================================================================

-- Lepaskan relasi pegawai agar tidak error foreign key
UPDATE `employees` SET `position_id` = NULL WHERE `position_id` IS NOT NULL;

DELETE FROM `positions`;

-- Reset auto increment (opsional)
ALTER TABLE `positions` AUTO_INCREMENT = 1;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- BAGIAN 2: TAMBAH JABATAN BARU (STANDAR RUMAH SAKIT)
-- =============================================================================

INSERT INTO `positions` (`code`, `name`, `description`, `is_active`, `created_at`, `updated_at`) VALUES

-- Pimpinan & Manajemen
('DIRUT',     'Direktur Utama',              'Pimpinan tertinggi rumah sakit',                          1, NOW(), NOW()),
('WADIR',     'Wakil Direktur',              'Wakil direktur rumah sakit',                              1, NOW(), NOW()),
('KABAG',     'Kepala Bagian',               'Kepala bagian/unit kerja',                                1, NOW(), NOW()),
('KAINS',     'Kepala Instalasi',            'Kepala instalasi layanan klinis/non-klinis',              1, NOW(), NOW()),
('SPV',       'Supervisor',                  'Pengawas operasional unit kerja',                         1, NOW(), NOW()),

-- Tenaga Medis — Dokter
('DR_UMUM',   'Dokter Umum',                 'Dokter umum / dokter jaga',                               1, NOW(), NOW()),
('DR_SP',     'Dokter Spesialis',            'Dokter spesialis (penyakit dalam, anak, bedah, dll.)',    1, NOW(), NOW()),
('DR_GIGI',   'Dokter Gigi',                 'Dokter gigi umum',                                        1, NOW(), NOW()),
('DR_SP_GIGI','Dokter Spesialis Gigi',       'Dokter spesialis konservasi gigi, ortodonti, dll.',       1, NOW(), NOW()),
('DR_IGD',    'Dokter IGD',                  'Dokter instalasi gawat darurat',                          1, NOW(), NOW()),

-- Tenaga Medis — Keperawatan & Kebidanan
('BIDAN',     'Bidan',                       'Tenaga kebidanan',                                        1, NOW(), NOW()),
('PERAWAT',   'Perawat',                     'Perawat pelaksana',                                       1, NOW(), NOW()),
('NERS',      'Perawat Ners',                'Perawat profesional (Ners)',                              1, NOW(), NOW()),
('ASPER',     'Asisten Perawat',             'Asisten perawat / tenaga pendukung keperawatan',          1, NOW(), NOW()),

-- Tenaga Medis — Farmasi & Laboratorium
('APTK',      'Apoteker',                    'Apoteker penanggung jawab farmasi',                       1, NOW(), NOW()),
('AS_APTK',   'Asisten Apoteker',            'Tenaga teknis kefarmasian',                               1, NOW(), NOW()),
('ANALIS',    'Analis Kesehatan',            'Analis laboratorium klinik',                              1, NOW(), NOW()),
('RADIO',     'Radiografer',                 'Tenaga radiologi / pencitraan medis',                     1, NOW(), NOW()),

-- Tenaga Medis — Rehabilitasi & Penunjang Klinis
('FISIO',     'Fisioterapis',                'Terapis fisioterapi dan rehabilitasi medis',              1, NOW(), NOW()),
('OKU',       'Okupasi Terapis',             'Terapis okupasi',                                         1, NOW(), NOW()),
('GIZI',      'Nutritionis / Dietisien',     'Ahli gizi klinik dan dietetik',                           1, NOW(), NOW()),
('PSIKO',     'Psikolog Klinis',             'Psikolog klinis rumah sakit',                             1, NOW(), NOW()),
('T_GIGI',    'Terapis Gigi',                'Terapis gigi dan mulut',                                  1, NOW(), NOW()),

-- Tenaga Kesehatan Lainnya
('SANITAR',   'Sanitarian',                  'Kesehatan lingkungan rumah sakit',                        1, NOW(), NOW()),
('REHAB_MED', 'Rehabilitasi Medik',          'Tenaga rehabilitasi medik',                               1, NOW(), NOW()),

-- Administrasi & Keuangan
('ADM',       'Staf Administrasi',           'Administrasi umum rumah sakit',                           1, NOW(), NOW()),
('RESEPS',    'Resepsionis',                 'Front office / pendaftaran pasien',                       1, NOW(), NOW()),
('HRD',       'Staf SDM / HRD',              'Sumber daya manusia',                                     1, NOW(), NOW()),
('KEU',       'Staf Keuangan',               'Keuangan dan akuntansi',                                  1, NOW(), NOW()),
('AKT',       'Akuntan',                     'Akuntan rumah sakit',                                     1, NOW(), NOW()),

-- IT, Humas & Umum
('IT',        'Staf IT',                     'Teknologi informasi rumah sakit',                         1, NOW(), NOW()),
('HUMAS',     'Humas',                       'Hubungan masyarakat',                                     1, NOW(), NOW()),
('LEGAL',     'Staf Hukum',                  'Legal dan kepatuhan',                                     1, NOW(), NOW()),

-- Penunjang Non-Klinis
('SATPAM',    'Satpam / Security',           'Keamanan rumah sakit',                                    1, NOW(), NOW()),
('HK',        'Housekeeping',                'Kebersihan ruangan dan area RS',                          1, NOW(), NOW()),
('CS',        'Cleaning Service',            'Petugas kebersihan',                                      1, NOW(), NOW()),
('LAUNDRY',   'Laundry',                     'Unit laundry linen rumah sakit',                          1, NOW(), NOW()),
('LOGIST',    'Logistik / Gudang',           'Gudang farmasi dan logistik',                             1, NOW(), NOW()),
('DRIVER',    'Driver Ambulans',             'Pengemudi ambulans',                                      1, NOW(), NOW()),
('BIOMED',    'Teknisi Biomedis',            'Pemeliharaan alat kesehatan',                             1, NOW(), NOW()),
('TEKNISI',   'Teknisi Umum',                'Teknisi bangunan dan utilitas RS',                        1, NOW(), NOW()),

-- Pendidikan & Penelitian (jika RS pendidikan)
('DOSEN',     'Dosen Klinik',                'Dosen klinik / preceptor',                                1, NOW(), NOW()),
('MAHASISWA','Mahasiswa Kesehatan',         'Mahasiswa koas / internship',                             1, NOW(), NOW());

-- =============================================================================
-- BAGIAN 3 (OPSIONAL): Set jabatan default untuk pegawai demo
-- Sesuaikan employee_number dengan data Anda
-- =============================================================================

-- UPDATE `employees` SET `position_id` = (SELECT `id` FROM `positions` WHERE `code` = 'DR_UMUM' LIMIT 1)
-- WHERE `employee_number` = 'EMP-001';

-- =============================================================================
-- SELESAI — Total: 42 jabatan standar rumah sakit
-- =============================================================================

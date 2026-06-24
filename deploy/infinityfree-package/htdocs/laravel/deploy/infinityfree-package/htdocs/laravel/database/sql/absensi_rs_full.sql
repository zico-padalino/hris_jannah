-- =============================================================================
-- ABSENSI RS — SQL LENGKAP
-- Database: MySQL 8+ / MariaDB 10.4+
-- Password demo semua user: password
-- Hash bcrypt: $2y$12$S42FPaDhYPRRMdWZfXd6/OBIU00X/zdT1qqovusD6exsqHpxCYu3i
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS attendances;
DROP TABLE IF EXISTS employee_faces;
DROP TABLE IF EXISTS employees;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS branch_locations;
DROP TABLE IF EXISTS branches;
DROP TABLE IF EXISTS personal_access_tokens;
DROP TABLE IF EXISTS failed_jobs;
DROP TABLE IF EXISTS job_batches;
DROP TABLE IF EXISTS jobs;
DROP TABLE IF EXISTS cache_locks;
DROP TABLE IF EXISTS cache;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS password_reset_tokens;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS migrations;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- BAGIAN 1: STRUKTUR TABEL (DDL)
-- =============================================================================

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(255) NOT NULL DEFAULT 'employee',
    branch_id BIGINT UNSIGNED NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX sessions_user_id_index (user_id),
    INDEX sessions_last_activity_index (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cache (
    `key` VARCHAR(255) PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL,
    INDEX cache_expiration_index (expiration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cache_locks (
    `key` VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL,
    INDEX cache_locks_expiration_index (expiration)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    INDEX jobs_queue_index (queue)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE job_batches (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INT NOT NULL,
    pending_jobs INT NOT NULL,
    failed_jobs INT NOT NULL,
    failed_job_ids LONGTEXT NOT NULL,
    options MEDIUMTEXT NULL,
    cancelled_at INT NULL,
    created_at INT NOT NULL,
    finished_at INT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX failed_jobs_connection_queue_failed_at_index (connection(191), queue(191), failed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name TEXT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX personal_access_tokens_tokenable_index (tokenable_type, tokenable_id),
    INDEX personal_access_tokens_expires_at_index (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE branches (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    address TEXT NULL,
    phone VARCHAR(255) NULL,
    city VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE branch_locations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    latitude DECIMAL(10, 7) NOT NULL,
    longitude DECIMAL(10, 7) NOT NULL,
    radius_meters INT UNSIGNED NOT NULL DEFAULT 100,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT branch_locations_branch_id_foreign
        FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE users
    ADD CONSTRAINT users_branch_id_foreign
        FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL;

CREATE TABLE departments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    branch_id BIGINT UNSIGNED NOT NULL,
    code VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY departments_branch_id_code_unique (branch_id, code),
    CONSTRAINT departments_branch_id_foreign
        FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE employees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL UNIQUE,
    branch_id BIGINT UNSIGNED NOT NULL,
    department_id BIGINT UNSIGNED NULL,
    employee_number VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(255) NULL,
    position VARCHAR(255) NULL,
    employment_status VARCHAR(255) NOT NULL DEFAULT 'permanent',
    base_salary DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    join_date DATE NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT employees_user_id_foreign
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT employees_branch_id_foreign
        FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    CONSTRAINT employees_department_id_foreign
        FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE employee_faces (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    photo_path VARCHAR(255) NOT NULL,
    face_descriptor JSON NOT NULL,
    is_primary TINYINT(1) NOT NULL DEFAULT 0,
    enrolled_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT employee_faces_employee_id_foreign
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE attendances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    branch_id BIGINT UNSIGNED NOT NULL,
    branch_location_id BIGINT UNSIGNED NULL,
    type VARCHAR(255) NOT NULL,
    attended_at TIMESTAMP NOT NULL,
    latitude DECIMAL(10, 7) NOT NULL,
    longitude DECIMAL(10, 7) NOT NULL,
    photo_path VARCHAR(255) NULL,
    face_match_score DECIMAL(5, 4) NULL,
    face_verified TINYINT(1) NOT NULL DEFAULT 0,
    location_verified TINYINT(1) NOT NULL DEFAULT 0,
    distance_meters INT UNSIGNED NULL,
    status VARCHAR(255) NOT NULL DEFAULT 'valid',
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX attendances_employee_id_attended_at_index (employee_id, attended_at),
    INDEX attendances_branch_id_attended_at_index (branch_id, attended_at),
    CONSTRAINT attendances_employee_id_foreign
        FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    CONSTRAINT attendances_branch_id_foreign
        FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
    CONSTRAINT attendances_branch_location_id_foreign
        FOREIGN KEY (branch_location_id) REFERENCES branch_locations(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- BAGIAN 2: DATA DEMO (DML — INSERT)
-- =============================================================================

INSERT INTO branches (id, code, name, address, phone, city, is_active, created_at, updated_at) VALUES
(1, 'RS-SRG', 'RS Cabang Serang', 'Jl. Raya Serang KM 3', '0254-123456', 'Serang', 1, NOW(), NOW()),
(2, 'RS-TNG', 'RS Cabang Tangerang', 'Jl. Sudirman No. 10', '021-7654321', 'Tangerang', 1, NOW(), NOW());

INSERT INTO branch_locations (id, branch_id, name, latitude, longitude, radius_meters, is_active, created_at, updated_at) VALUES
(1, 1, 'Gerbang Utama Serang', -6.1188370, 106.1536790, 150, 1, NOW(), NOW()),
(2, 1, 'Lobby IGD Serang', -6.1192000, 106.1541000, 100, 1, NOW(), NOW()),
(3, 2, 'Pintu Masuk Tangerang', -6.1784840, 106.6317690, 120, 1, NOW(), NOW());

INSERT INTO departments (id, branch_id, code, name, is_active, created_at, updated_at) VALUES
(1, 1, 'IGD', 'Instalasi Gawat Darurat', 1, NOW(), NOW()),
(2, 2, 'FAR', 'Farmasi', 1, NOW(), NOW());

INSERT INTO users (id, name, email, password, role, branch_id, is_active, created_at, updated_at) VALUES
(1, 'Super Admin', 'admin@rs.local', '$2y$12$S42FPaDhYPRRMdWZfXd6/OBIU00X/zdT1qqovusD6exsqHpxCYu3i', 'super_admin', NULL, 1, NOW(), NOW()),
(2, 'HRD Pusat', 'hrd@rs.local', '$2y$12$S42FPaDhYPRRMdWZfXd6/OBIU00X/zdT1qqovusD6exsqHpxCYu3i', 'hr', NULL, 1, NOW(), NOW()),
(3, 'Admin Serang', 'admin.serang@rs.local', '$2y$12$S42FPaDhYPRRMdWZfXd6/OBIU00X/zdT1qqovusD6exsqHpxCYu3i', 'branch_admin', 1, 1, NOW(), NOW()),
(4, 'Dr. Budi Santoso', 'budi@rs.local', '$2y$12$S42FPaDhYPRRMdWZfXd6/OBIU00X/zdT1qqovusD6exsqHpxCYu3i', 'employee', 1, 1, NOW(), NOW());

INSERT INTO employees (id, user_id, branch_id, department_id, employee_number, name, email, phone, position, employment_status, base_salary, join_date, is_active, created_at, updated_at) VALUES
(1, 4, 1, 1, 'EMP-001', 'Dr. Budi Santoso', 'budi@rs.local', '081234567890', 'Dokter IGD', 'permanent', 15000000.00, '2020-01-15', 1, NOW(), NOW());

-- face_descriptor demo: 128 angka (sin-based, seed=1)
INSERT INTO employee_faces (id, employee_id, photo_path, face_descriptor, is_primary, enrolled_at, created_at, updated_at) VALUES
(1, 1, 'faces/demo/employee-001.jpg', '[-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464,-0.041580662969464,0.041580662969464]', 1, NOW(), NOW(), NOW());

-- Contoh data absensi demo
INSERT INTO attendances (employee_id, branch_id, branch_location_id, type, attended_at, latitude, longitude, photo_path, face_match_score, face_verified, location_verified, distance_meters, status, notes, created_at, updated_at) VALUES
(1, 1, 1, 'check_in', CONCAT(CURDATE(), ' 07:55:00'), -6.1188370, 106.1536790, NULL, 0.9800, 1, 1, 12, 'valid', 'Absen masuk valid', NOW(), NOW()),
(1, 1, 1, 'check_out', CONCAT(CURDATE(), ' 16:05:00'), -6.1189000, 106.1537000, NULL, 0.9750, 1, 1, 18, 'valid', 'Absen pulang valid', NOW(), NOW()),
(1, 1, NULL, 'check_in', CONCAT(CURDATE(), ' 08:10:00'), -6.2000000, 106.2000000, NULL, 0.9200, 1, 0, NULL, 'invalid_location', 'Di luar radius geofence', NOW(), NOW());

-- =============================================================================
-- BAGIAN 3: QUERY SELECT — SEMUA FITUR
-- =============================================================================

-- -----------------------------------------------------------------------------
-- 3.1 USERS & AUTH
-- -----------------------------------------------------------------------------

-- Login user by email
SELECT id, name, email, password, role, branch_id, is_active
FROM users
WHERE email = 'admin@rs.local'
  AND is_active = 1;

-- Daftar semua user dengan cabang
SELECT u.id, u.name, u.email, u.role, b.name AS cabang, u.is_active
FROM users u
LEFT JOIN branches b ON b.id = u.branch_id
ORDER BY u.id;

-- User pegawai beserta data employee
SELECT u.id, u.name, u.email, e.employee_number, e.position, b.name AS cabang
FROM users u
JOIN employees e ON e.user_id = u.id
JOIN branches b ON b.id = e.branch_id
WHERE u.role = 'employee';

-- -----------------------------------------------------------------------------
-- 3.2 CABANG RS (MULTI CABANG)
-- -----------------------------------------------------------------------------

-- Semua cabang aktif
SELECT id, code, name, city, phone, is_active
FROM branches
WHERE is_active = 1
ORDER BY name;

-- Cabang dengan jumlah pegawai & lokasi
SELECT
    b.id,
    b.code,
    b.name,
    COUNT(DISTINCT e.id) AS total_pegawai,
    COUNT(DISTINCT bl.id) AS total_lokasi
FROM branches b
LEFT JOIN employees e ON e.branch_id = b.id AND e.is_active = 1
LEFT JOIN branch_locations bl ON bl.branch_id = b.id AND bl.is_active = 1
GROUP BY b.id, b.code, b.name
ORDER BY b.name;

-- Detail satu cabang
SELECT *
FROM branches
WHERE code = 'RS-SRG';

-- Tambah cabang (contoh)
-- INSERT INTO branches (code, name, address, phone, city, is_active, created_at, updated_at)
-- VALUES ('RS-BDG', 'RS Cabang Bandung', 'Jl. Pasteur', '022-111222', 'Bandung', 1, NOW(), NOW());

-- Update cabang
-- UPDATE branches SET phone = '0254-999888', updated_at = NOW() WHERE code = 'RS-SRG';

-- Nonaktifkan cabang
-- UPDATE branches SET is_active = 0, updated_at = NOW() WHERE code = 'RS-TNG';

-- Hapus cabang (cascade ke lokasi, departemen, pegawai, absensi)
-- DELETE FROM branches WHERE code = 'RS-BDG';

-- -----------------------------------------------------------------------------
-- 3.3 LOKASI ABSENSI (GEOFENCE — BISA DIATUR)
-- -----------------------------------------------------------------------------

-- Semua lokasi absensi per cabang
SELECT
    bl.id,
    b.name AS cabang,
    bl.name AS lokasi,
    bl.latitude,
    bl.longitude,
    bl.radius_meters,
    bl.is_active
FROM branch_locations bl
JOIN branches b ON b.id = bl.branch_id
ORDER BY b.name, bl.name;

-- Lokasi aktif cabang Serang
SELECT id, name, latitude, longitude, radius_meters
FROM branch_locations
WHERE branch_id = 1
  AND is_active = 1;

-- Tambah lokasi geofence baru
-- INSERT INTO branch_locations (branch_id, name, latitude, longitude, radius_meters, is_active, created_at, updated_at)
-- VALUES (1, 'Parkir RS Serang', -6.1195000, 106.1545000, 80, 1, NOW(), NOW());

-- Update radius lokasi
-- UPDATE branch_locations SET radius_meters = 200, updated_at = NOW() WHERE id = 1;

-- Cek apakah koordinat GPS dalam radius (Haversine — MySQL)
-- Ganti @lat, @lng dengan koordinat pegawai saat absen
SET @lat = -6.1188370;
SET @lng = 106.1536790;

SELECT
    bl.id,
    bl.name,
    bl.radius_meters,
    (
        6371000 * ACOS(
            LEAST(1, GREATEST(-1,
                COS(RADIANS(@lat)) * COS(RADIANS(bl.latitude)) *
                COS(RADIANS(bl.longitude) - RADIANS(@lng)) +
                SIN(RADIANS(@lat)) * SIN(RADIANS(bl.latitude))
            ))
        )
    ) AS jarak_meter,
    CASE
        WHEN (
            6371000 * ACOS(
                LEAST(1, GREATEST(-1,
                    COS(RADIANS(@lat)) * COS(RADIANS(bl.latitude)) *
                    COS(RADIANS(bl.longitude) - RADIANS(@lng)) +
                    SIN(RADIANS(@lat)) * SIN(RADIANS(bl.latitude))
                ))
            )
        ) <= bl.radius_meters THEN 'VALID'
        ELSE 'INVALID'
    END AS status_lokasi
FROM branch_locations bl
WHERE bl.branch_id = 1
  AND bl.is_active = 1;

-- -----------------------------------------------------------------------------
-- 3.4 DEPARTEMEN
-- -----------------------------------------------------------------------------

-- Semua departemen
SELECT d.id, b.name AS cabang, d.code, d.name AS departemen, d.is_active
FROM departments d
JOIN branches b ON b.id = d.branch_id
ORDER BY b.name, d.name;

-- Departemen per cabang
SELECT code, name, is_active
FROM departments
WHERE branch_id = 1;

-- Tambah departemen
-- INSERT INTO departments (branch_id, code, name, is_active, created_at, updated_at)
-- VALUES (1, 'RAW', 'Rawat Inap', 1, NOW(), NOW());

-- -----------------------------------------------------------------------------
-- 3.5 PEGAWAI
-- -----------------------------------------------------------------------------

-- Daftar pegawai lengkap
SELECT
    e.id,
    e.employee_number,
    e.name,
    b.name AS cabang,
    d.name AS departemen,
    e.position,
    e.employment_status,
    e.base_salary,
    e.join_date,
    e.is_active
FROM employees e
JOIN branches b ON b.id = e.branch_id
LEFT JOIN departments d ON d.id = e.department_id
ORDER BY b.name, e.name;

-- Cari pegawai
SELECT *
FROM employees
WHERE name LIKE '%Budi%'
   OR employee_number LIKE '%EMP%';

-- Pegawai per cabang
SELECT employee_number, name, position, base_salary
FROM employees
WHERE branch_id = 1
  AND is_active = 1;

-- Tambah pegawai
-- INSERT INTO employees (branch_id, department_id, employee_number, name, email, phone, position, employment_status, base_salary, join_date, is_active, created_at, updated_at)
-- VALUES (1, 1, 'EMP-002', 'Siti Aminah', 'siti@rs.local', '0811111111', 'Perawat IGD', 'permanent', 8000000.00, '2021-06-01', 1, NOW(), NOW());

-- Update gaji pokok
-- UPDATE employees SET base_salary = 16000000.00, updated_at = NOW() WHERE employee_number = 'EMP-001';

-- -----------------------------------------------------------------------------
-- 3.6 WAJAH PEGAWAI (FACE SCAN)
-- -----------------------------------------------------------------------------

-- Wajah terdaftar per pegawai
SELECT
    ef.id,
    e.employee_number,
    e.name,
    ef.photo_path,
    ef.is_primary,
    ef.enrolled_at
FROM employee_faces ef
JOIN employees e ON e.id = ef.employee_id
ORDER BY e.name, ef.is_primary DESC;

-- Wajah utama pegawai
SELECT e.name, ef.photo_path, ef.face_descriptor, ef.enrolled_at
FROM employee_faces ef
JOIN employees e ON e.id = ef.employee_id
WHERE ef.is_primary = 1;

-- Pegawai belum daftar wajah
SELECT e.employee_number, e.name, b.name AS cabang
FROM employees e
JOIN branches b ON b.id = e.branch_id
LEFT JOIN employee_faces ef ON ef.employee_id = e.id
WHERE ef.id IS NULL
  AND e.is_active = 1;

-- Daftarkan wajah (face_descriptor dari hasil scan face-api.js)
-- INSERT INTO employee_faces (employee_id, photo_path, face_descriptor, is_primary, enrolled_at, created_at, updated_at)
-- VALUES (1, 'faces/1/wajah.jpg', '[0.12, -0.05, ...128 angka...]', 1, NOW(), NOW(), NOW());

-- -----------------------------------------------------------------------------
-- 3.7 ABSENSI
-- -----------------------------------------------------------------------------

-- Semua riwayat absensi
SELECT
    a.id,
    a.attended_at,
    e.employee_number,
    e.name AS pegawai,
    b.name AS cabang,
    bl.name AS lokasi,
    a.type,
    a.face_match_score,
    a.distance_meters,
    a.face_verified,
    a.location_verified,
    a.status
FROM attendances a
JOIN employees e ON e.id = a.employee_id
JOIN branches b ON b.id = a.branch_id
LEFT JOIN branch_locations bl ON bl.id = a.branch_location_id
ORDER BY a.attended_at DESC;

-- Absensi hari ini
SELECT
    e.name AS pegawai,
    a.type,
    a.attended_at,
    a.status,
    bl.name AS lokasi
FROM attendances a
JOIN employees e ON e.id = a.employee_id
LEFT JOIN branch_locations bl ON bl.id = a.branch_location_id
WHERE DATE(a.attended_at) = CURDATE()
ORDER BY a.attended_at;

-- Absensi invalid (wajah / lokasi gagal)
SELECT
    a.attended_at,
    e.name,
    a.type,
    a.status,
    a.face_verified,
    a.location_verified,
    a.notes
FROM attendances a
JOIN employees e ON e.id = a.employee_id
WHERE a.status <> 'valid'
ORDER BY a.attended_at DESC;

-- Absensi per cabang & periode
SELECT
    b.name AS cabang,
    DATE(a.attended_at) AS tanggal,
    COUNT(*) AS total_absensi,
    SUM(CASE WHEN a.status = 'valid' THEN 1 ELSE 0 END) AS valid,
    SUM(CASE WHEN a.status <> 'valid' THEN 1 ELSE 0 END) AS invalid
FROM attendances a
JOIN branches b ON b.id = a.branch_id
WHERE a.attended_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
GROUP BY b.name, DATE(a.attended_at)
ORDER BY tanggal DESC, cabang;

-- Riwayat absensi satu pegawai
SELECT
    a.attended_at,
    a.type,
    bl.name AS lokasi,
    a.face_match_score,
    a.distance_meters,
    a.status
FROM attendances a
LEFT JOIN branch_locations bl ON bl.id = a.branch_location_id
WHERE a.employee_id = 1
ORDER BY a.attended_at DESC;

-- Rekap check-in & check-out harian pegawai
SELECT
    e.name,
    DATE(a.attended_at) AS tanggal,
    MIN(CASE WHEN a.type = 'check_in' THEN a.attended_at END) AS jam_masuk,
    MAX(CASE WHEN a.type = 'check_out' THEN a.attended_at END) AS jam_pulang
FROM attendances a
JOIN employees e ON e.id = a.employee_id
WHERE a.status = 'valid'
GROUP BY e.name, DATE(a.attended_at)
ORDER BY tanggal DESC, e.name;

-- Input absensi manual (contoh)
-- INSERT INTO attendances (employee_id, branch_id, branch_location_id, type, attended_at, latitude, longitude, face_match_score, face_verified, location_verified, distance_meters, status, created_at, updated_at)
-- VALUES (1, 1, 1, 'check_in', NOW(), -6.1188370, 106.1536790, 0.95, 1, 1, 10, 'valid', NOW(), NOW());

-- -----------------------------------------------------------------------------
-- 3.8 DASHBOARD / STATISTIK
-- -----------------------------------------------------------------------------

-- Statistik dashboard
SELECT
    (SELECT COUNT(*) FROM branches WHERE is_active = 1) AS cabang_aktif,
    (SELECT COUNT(*) FROM employees WHERE is_active = 1) AS pegawai_aktif,
    (SELECT COUNT(*) FROM attendances WHERE DATE(attended_at) = CURDATE()) AS absensi_hari_ini,
    (SELECT COUNT(*) FROM attendances WHERE DATE(attended_at) = CURDATE() AND status <> 'valid') AS invalid_hari_ini;

-- Statistik per cabang
SELECT
    b.name AS cabang,
    COUNT(DISTINCT e.id) AS pegawai,
    COUNT(a.id) AS total_absensi_bulan_ini,
    SUM(CASE WHEN a.status = 'valid' THEN 1 ELSE 0 END) AS absensi_valid
FROM branches b
LEFT JOIN employees e ON e.branch_id = b.id AND e.is_active = 1
LEFT JOIN attendances a ON a.branch_id = b.id
    AND a.attended_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
GROUP BY b.id, b.name;

-- -----------------------------------------------------------------------------
-- 3.9 PAYROLL / GAJI (DASAR — SIAP DIKEMBANGKAN)
-- -----------------------------------------------------------------------------

-- Daftar gaji pokok semua pegawai
SELECT
    e.employee_number,
    e.name,
    b.name AS cabang,
    d.name AS departemen,
    e.employment_status,
    e.base_salary AS gaji_pokok
FROM employees e
JOIN branches b ON b.id = e.branch_id
LEFT JOIN departments d ON d.id = e.department_id
WHERE e.is_active = 1
ORDER BY b.name, e.base_salary DESC;

-- Total gaji pokok per cabang
SELECT
    b.name AS cabang,
    COUNT(e.id) AS jumlah_pegawai,
    SUM(e.base_salary) AS total_gaji_pokok
FROM branches b
JOIN employees e ON e.branch_id = b.id AND e.is_active = 1
GROUP BY b.id, b.name;

-- Pegawai dengan absensi invalid (potensi potongan gaji)
SELECT
    e.employee_number,
    e.name,
    e.base_salary,
    COUNT(a.id) AS jumlah_absensi_invalid
FROM employees e
JOIN attendances a ON a.employee_id = e.id
WHERE a.status <> 'valid'
  AND a.attended_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
GROUP BY e.id, e.employee_number, e.name, e.base_salary
ORDER BY jumlah_absensi_invalid DESC;

-- -----------------------------------------------------------------------------
-- 3.10 API TOKEN (SANCTUM)
-- -----------------------------------------------------------------------------

-- Token aktif user
SELECT pat.id, u.email, pat.name, pat.last_used_at, pat.created_at
FROM personal_access_tokens pat
JOIN users u ON u.id = pat.tokenable_id AND pat.tokenable_type = 'App\\Models\\User'
ORDER BY pat.created_at DESC;

-- Hapus semua token user
-- DELETE FROM personal_access_tokens WHERE tokenable_type = 'App\\Models\\User' AND tokenable_id = 1;

-- =============================================================================
-- BAGIAN 4: QUERY UTILITY
-- =============================================================================

-- Reset auto increment (setelah import manual)
-- ALTER TABLE branches AUTO_INCREMENT = 3;
-- ALTER TABLE users AUTO_INCREMENT = 5;
-- ALTER TABLE employees AUTO_INCREMENT = 2;

-- Backup ringkas semua data bisnis
SELECT 'branches' AS tabel, COUNT(*) AS jumlah FROM branches
UNION ALL SELECT 'branch_locations', COUNT(*) FROM branch_locations
UNION ALL SELECT 'departments', COUNT(*) FROM departments
UNION ALL SELECT 'employees', COUNT(*) FROM employees
UNION ALL SELECT 'employee_faces', COUNT(*) FROM employee_faces
UNION ALL SELECT 'attendances', COUNT(*) FROM attendances
UNION ALL SELECT 'users', COUNT(*) FROM users;

-- Hapus semua data absensi (reset uji coba)
-- DELETE FROM attendances;

-- Hapus semua data kecuali user admin
-- SET FOREIGN_KEY_CHECKS = 0;
-- TRUNCATE attendances;
-- TRUNCATE employee_faces;
-- TRUNCATE employees;
-- TRUNCATE departments;
-- TRUNCATE branch_locations;
-- TRUNCATE branches;
-- SET FOREIGN_KEY_CHECKS = 1;

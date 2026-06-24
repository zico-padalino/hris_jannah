-- =============================================================================
-- SATU QUERY: Jam Kerja Pegawai + Absensi + Keterlambatan
-- Database: absensi_rs (MySQL)
-- Tidak butuh kolom is_late / late_minutes (dihitung otomatis dari jadwal)
-- Opsional: ubah filter di baris WHERE (hapus tanda --)
-- =============================================================================

SELECT
    a.id AS attendance_id,
    DATE(a.attended_at) AS tanggal,
    TIME(a.attended_at) AS jam_absen,
    a.attended_at AS waktu_lengkap,
    e.employee_number AS nip,
    e.name AS pegawai,
    e.fingerprint_pin AS pin_mesin,
    b.name AS cabang,
    d.name AS departemen,
    s.code AS kode_jadwal,
    s.name AS nama_jadwal,
    TIME_FORMAT(s.start_time, '%H:%i') AS jam_masuk_jadwal,
    TIME_FORMAT(s.end_time, '%H:%i') AS jam_pulang_jadwal,
    s.work_days AS hari_kerja,
    s.late_tolerance_minutes AS toleransi_mnt,
    a.type AS tipe_absensi,
    a.source AS sumber,
    a.status AS status_absensi,
    CASE
        WHEN a.type <> 'check_in' THEN 0
        WHEN s.id IS NULL THEN 0
        WHEN TIMESTAMPDIFF(
            MINUTE,
            CONCAT(DATE(a.attended_at), ' ', s.start_time),
            a.attended_at
        ) > s.late_tolerance_minutes THEN 1
        ELSE 0
    END AS is_late,
    CASE
        WHEN a.type <> 'check_in' THEN NULL
        WHEN s.id IS NULL THEN NULL
        WHEN TIMESTAMPDIFF(
            MINUTE,
            CONCAT(DATE(a.attended_at), ' ', s.start_time),
            a.attended_at
        ) > s.late_tolerance_minutes THEN TIMESTAMPDIFF(
            MINUTE,
            CONCAT(DATE(a.attended_at), ' ', s.start_time),
            a.attended_at
        )
        ELSE NULL
    END AS late_minutes,
    TIMESTAMPDIFF(
        MINUTE,
        CONCAT(DATE(a.attended_at), ' ', s.start_time),
        a.attended_at
    ) AS selisih_mnt_dari_jadwal,
    CASE
        WHEN a.type <> 'check_in' THEN 'Pulang / bukan masuk'
        WHEN s.id IS NULL THEN 'Tanpa jadwal'
        WHEN TIMESTAMPDIFF(
            MINUTE,
            CONCAT(DATE(a.attended_at), ' ', s.start_time),
            a.attended_at
        ) <= s.late_tolerance_minutes THEN 'Tepat waktu'
        ELSE CONCAT(
            'Terlambat ',
            TIMESTAMPDIFF(
                MINUTE,
                CONCAT(DATE(a.attended_at), ' ', s.start_time),
                a.attended_at
            ),
            ' mnt'
        )
    END AS evaluasi,
    CASE
        WHEN a.status IN ('late', 'invalid_face', 'invalid_location', 'invalid_both') THEN
            CAST(ss.value AS DECIMAL(15, 2))
        ELSE 0
    END AS potongan_gaji,
    a.notes AS keterangan
FROM attendances a
INNER JOIN employees e ON e.id = a.employee_id
INNER JOIN branches b ON b.id = a.branch_id
LEFT JOIN departments d ON d.id = e.department_id
LEFT JOIN shifts s ON s.id = e.shift_id
LEFT JOIN system_settings ss ON ss.`key` = 'payroll_deduction_invalid'
WHERE e.is_active = 1
  -- AND DATE(a.attended_at) = CURDATE()
  -- AND DATE(a.attended_at) = '2026-06-21'
  -- AND b.id = 1
ORDER BY a.attended_at DESC;

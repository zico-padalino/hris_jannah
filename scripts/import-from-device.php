<?php

/**
 * Impor log absensi langsung dari mesin ZKTeco via TCP port 4370.
 * Dipakai jika ADMS push HTTP tidak mengirim data ke Laravel.
 *
 * Usage: php scripts/import-from-device.php
 */

require __DIR__.'/../vendor/autoload.php';

use CodingLibs\ZktecoPhp\Libs\ZKTeco;

$deviceIp = '192.168.1.250';
$deviceId = 2;
$branchId = 1;
$serialNumber = '33983250932';

$pdo = new PDO(
    'mysql:host=127.0.0.1;port=3306;dbname=absensi_rs;charset=utf8mb4',
    'root',
    '200122',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
);

$employees = [];
foreach ($pdo->query('SELECT id, fingerprint_pin FROM employees WHERE branch_id = '.$branchId.' AND fingerprint_pin IS NOT NULL AND is_active = 1') as $row) {
    $employees[(string) $row['fingerprint_pin']] = (int) $row['id'];
}

$zk = new ZKTeco($deviceIp, 4370, true, 15);
if (! $zk->connect()) {
    echo "Gagal koneksi ke mesin {$deviceIp}:4370\n";
    exit(1);
}

$records = $zk->getAttendances();
$zk->disconnect();

echo count($records)." record di mesin\n";

$processed = 0;
$skipped = 0;
$failed = 0;

$checkLog = $pdo->prepare('SELECT id FROM fingerprint_logs WHERE fingerprint_device_id = ? AND device_pin = ? AND punched_at = ? AND punch_status = ?');
$insertLog = $pdo->prepare('INSERT INTO fingerprint_logs (fingerprint_device_id, device_pin, punched_at, punch_status, verify_mode, raw_line, employee_id, process_status, process_message, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,?,NOW(),NOW())');
$insertAtt = $pdo->prepare('INSERT INTO attendances (employee_id, branch_id, fingerprint_device_id, type, source, attended_at, latitude, longitude, face_verified, location_verified, status, notes, created_at, updated_at) VALUES (?,?,?,?,?,?,0,0,1,1,?,?,NOW(),NOW())');
$updateLog = $pdo->prepare('UPDATE fingerprint_logs SET process_status = ?, attendance_id = ?, updated_at = NOW() WHERE id = ?');

foreach ($records as $record) {
    $pin = (string) ($record['user_id'] ?? '');
    $punchedAt = $record['record_time'] ?? null;
    $status = (int) ($record['state'] ?? 0);

    if ($pin === '' || $punchedAt === null) {
        $failed++;
        continue;
    }

    $rawLine = implode("\t", [$pin, $punchedAt, $status, 1]);
    $checkLog->execute([$deviceId, $pin, $punchedAt, $status]);
    if ($checkLog->fetchColumn()) {
        $skipped++;
        continue;
    }

    $employeeId = $employees[$pin] ?? null;
    $processStatus = 'pending';
    $message = null;

    if ($employeeId === null) {
        $processStatus = 'failed';
        $message = 'PIN tidak terdaftar di sistem.';
        $insertLog->execute([$deviceId, $pin, $punchedAt, $status, 1, $rawLine, null, $processStatus, $message]);
        $failed++;
        continue;
    }

    $type = in_array($status, [1, 3, 5], true) ? 'check_out' : 'check_in';
    $insertAtt->execute([
        $employeeId,
        $branchId,
        $deviceId,
        $type,
        'fingerprint',
        $punchedAt,
        'valid',
        'Absensi via mesin fingerprint '.$serialNumber,
    ]);
    $attendanceId = (int) $pdo->lastInsertId();

    $insertLog->execute([$deviceId, $pin, $punchedAt, $status, 1, $rawLine, $employeeId, 'processed', null]);
    $logId = (int) $pdo->lastInsertId();
    $updateLog->execute(['processed', $attendanceId, $logId]);

    $processed++;
}

echo "Selesai: {$processed} baru, {$skipped} duplikat, {$failed} gagal\n";

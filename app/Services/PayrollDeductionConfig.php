<?php

namespace App\Services;

use App\Models\SystemSetting;

class PayrollDeductionConfig
{
    /** @return array<string, mixed> */
    public function all(): array
    {
        return [
            'attendance_amount' => (float) SystemSetting::getValue('payroll_deduction_invalid', 50000),
            'pph21_enabled' => (bool) SystemSetting::getValue('payroll_pph21_enabled', false),
            'pph21_rate' => (float) SystemSetting::getValue('payroll_pph21_rate', 5),
            'bpjs_kes_enabled' => (bool) SystemSetting::getValue('payroll_bpjs_kes_enabled', false),
            'bpjs_kes_employee_rate' => (float) SystemSetting::getValue('payroll_bpjs_kes_employee_rate', 1),
            'bpjs_kes_employer_rate' => (float) SystemSetting::getValue('payroll_bpjs_kes_employer_rate', 4),
            'bpjs_kes_salary_cap' => (float) SystemSetting::getValue('payroll_bpjs_kes_salary_cap', 12000000),
            'bpjs_tk_enabled' => (bool) SystemSetting::getValue('payroll_bpjs_tk_enabled', false),
            'bpjs_tk_jht_employee_rate' => (float) SystemSetting::getValue('payroll_bpjs_tk_jht_employee_rate', 2),
            'bpjs_tk_jht_employer_rate' => (float) SystemSetting::getValue('payroll_bpjs_tk_jht_employer_rate', 3.7),
            'bpjs_tk_jp_employee_rate' => (float) SystemSetting::getValue('payroll_bpjs_tk_jp_employee_rate', 1),
            'bpjs_tk_jp_employer_rate' => (float) SystemSetting::getValue('payroll_bpjs_tk_jp_employer_rate', 2),
            'bpjs_tk_jkm_employer_rate' => (float) SystemSetting::getValue('payroll_bpjs_tk_jkm_employer_rate', 0.3),
            'bpjs_tk_jkk_employer_rate' => (float) SystemSetting::getValue('payroll_bpjs_tk_jkk_employer_rate', 0.24),
            'bpjs_tk_jp_salary_cap' => (float) SystemSetting::getValue('payroll_bpjs_tk_jp_salary_cap', 10547400),
        ];
    }

    /** @param array<string, mixed> $data */
    public function save(array $data): void
    {
        SystemSetting::setValue('payroll_deduction_invalid', $data['attendance_amount'], 'Potongan per absensi terlambat/invalid');
        SystemSetting::setValue('payroll_pph21_enabled', $data['pph21_enabled'] ? '1' : '0', 'Aktifkan potongan PPh 21');
        SystemSetting::setValue('payroll_pph21_rate', $data['pph21_rate'], 'Tarif efektif PPh 21 (%)');
        SystemSetting::setValue('payroll_bpjs_kes_enabled', $data['bpjs_kes_enabled'] ? '1' : '0', 'Aktifkan BPJS Kesehatan');
        SystemSetting::setValue('payroll_bpjs_kes_employee_rate', $data['bpjs_kes_employee_rate'], 'BPJS Kesehatan pegawai (%)');
        SystemSetting::setValue('payroll_bpjs_kes_employer_rate', $data['bpjs_kes_employer_rate'], 'BPJS Kesehatan perusahaan (%)');
        SystemSetting::setValue('payroll_bpjs_kes_salary_cap', $data['bpjs_kes_salary_cap'], 'Batas upah BPJS Kesehatan');
        SystemSetting::setValue('payroll_bpjs_tk_enabled', $data['bpjs_tk_enabled'] ? '1' : '0', 'Aktifkan BPJS Ketenagakerjaan');
        SystemSetting::setValue('payroll_bpjs_tk_jht_employee_rate', $data['bpjs_tk_jht_employee_rate'], 'JHT pegawai (%)');
        SystemSetting::setValue('payroll_bpjs_tk_jht_employer_rate', $data['bpjs_tk_jht_employer_rate'], 'JHT perusahaan (%)');
        SystemSetting::setValue('payroll_bpjs_tk_jp_employee_rate', $data['bpjs_tk_jp_employee_rate'], 'JP pegawai (%)');
        SystemSetting::setValue('payroll_bpjs_tk_jp_employer_rate', $data['bpjs_tk_jp_employer_rate'], 'JP perusahaan (%)');
        SystemSetting::setValue('payroll_bpjs_tk_jkm_employer_rate', $data['bpjs_tk_jkm_employer_rate'], 'JKM perusahaan (%)');
        SystemSetting::setValue('payroll_bpjs_tk_jkk_employer_rate', $data['bpjs_tk_jkk_employer_rate'], 'JKK perusahaan (%)');
        SystemSetting::setValue('payroll_bpjs_tk_jp_salary_cap', $data['bpjs_tk_jp_salary_cap'], 'Batas upah JP');
    }

    public function attendanceAmount(): float
    {
        return (float) SystemSetting::getValue('payroll_deduction_invalid', 50000);
    }

    /**
     * @return array{
     *     attendance: float,
     *     pph21: float,
     *     bpjs_kes_employee: float,
     *     bpjs_tk_employee: float,
     *     total: float,
     *     notes: list<string>
     * }
     */
    public function calculate(float $baseSalary, float $allowances, int $attendanceDeductibleCount): array
    {
        $config = $this->all();
        $gross = $baseSalary + $allowances;

        $attendance = $attendanceDeductibleCount * (float) $config['attendance_amount'];

        $pph21 = $config['pph21_enabled']
            ? round($gross * ((float) $config['pph21_rate'] / 100), 2)
            : 0.0;

        $bpjsKesBase = min($gross, (float) $config['bpjs_kes_salary_cap']);
        $bpjsKesEmployee = $config['bpjs_kes_enabled']
            ? round($bpjsKesBase * ((float) $config['bpjs_kes_employee_rate'] / 100), 2)
            : 0.0;

        $bpjsTkJpBase = min($gross, (float) $config['bpjs_tk_jp_salary_cap']);
        $bpjsTkEmployee = 0.0;

        if ($config['bpjs_tk_enabled']) {
            $bpjsTkEmployee = round($gross * ((float) $config['bpjs_tk_jht_employee_rate'] / 100), 2)
                + round($bpjsTkJpBase * ((float) $config['bpjs_tk_jp_employee_rate'] / 100), 2);
        }

        $total = $attendance + $pph21 + $bpjsKesEmployee + $bpjsTkEmployee;

        $notes = [];

        if ($attendanceDeductibleCount > 0) {
            $notes[] = "Absensi {$attendanceDeductibleCount}x @ Rp ".number_format((float) $config['attendance_amount'], 0, ',', '.');
        }

        if ($pph21 > 0) {
            $notes[] = 'PPh 21 '.rtrim(rtrim(number_format((float) $config['pph21_rate'], 2, ',', '.'), '0'), ',').'%';
        }

        if ($bpjsKesEmployee > 0) {
            $notes[] = 'BPJS Kes pegawai';
        }

        if ($bpjsTkEmployee > 0) {
            $notes[] = 'BPJS TK pegawai';
        }

        return [
            'attendance' => $attendance,
            'pph21' => $pph21,
            'bpjs_kes_employee' => $bpjsKesEmployee,
            'bpjs_tk_employee' => $bpjsTkEmployee,
            'total' => $total,
            'notes' => $notes,
        ];
    }
}

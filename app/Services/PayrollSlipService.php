<?php

namespace App\Services;

use App\Enums\PayrollSlipSignatureStatus;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;

class PayrollSlipService
{
    public function __construct(
        private readonly PayrollService $payrollService,
        private readonly PayrollDeductionConfig $deductionConfig,
        private readonly PayrollSlipConfig $slipConfig,
        private readonly PayrollSlipSignatureService $signatureService,
    ) {}

    /** @return array<string, mixed> */
    public function build(PayrollItem $item, PayrollPeriod $period): array
    {
        $item->load(['employee.branch', 'employee.department', 'employee.position']);
        $period->load('branch');

        $employee = $item->employee;
        $deductibleCount = $this->payrollService
            ->deductibleAttendances($employee, $period)
            ->count();

        $baseSalary = (float) $item->base_salary;
        $allowances = (float) $item->allowances;
        $breakdown = $this->deductionConfig->calculate($baseSalary, $allowances, $deductibleCount);
        $gross = $baseSalary + $allowances;

        $verificationCode = $this->verificationCode($period, $item);
        $signature = $this->signatureService->forItem($item);
        $signatureApproved = $signature?->status === PayrollSlipSignatureStatus::Approved;

        return [
            'item' => $item,
            'period' => $period,
            'employee' => $employee,
            'gross' => $gross,
            'breakdown' => $breakdown,
            'deductible_count' => $deductibleCount,
            'verification_code' => $verificationCode,
            'verification_url' => route('payroll-slip.verify', ['code' => $verificationCode]),
            'scan_text' => $this->scanText($verificationCode, $employee->name, $employee->employee_number, $this->slipConfig->hrdName()),
            'issued_at' => now(),
            'hrd_name' => $this->slipConfig->hrdName(),
            'hrd_title' => $this->slipConfig->hrdTitle(),
            'hrd_signature_url' => $this->slipConfig->signatureUrl(),
            'has_hrd_signature' => $this->slipConfig->hasSignature(),
            'signature' => $signature,
            'signature_approved' => $signatureApproved,
            'can_request_signature' => $signature === null,
            'signature_pending' => $signature?->status === PayrollSlipSignatureStatus::Pending,
        ];
    }

    /** @return array<string, mixed>|null */
    public function verify(string $code): ?array
    {
        if (! preg_match('/^SLIP-(\d{6})-(.+)-([A-F0-9]{8})$/', $code, $matches)) {
            return null;
        }

        $year = (int) substr($matches[1], 0, 4);
        $month = (int) substr($matches[1], 4, 2);
        $employeeNumber = $matches[2];
        $hash = $matches[3];

        $employee = \App\Models\Employee::query()
            ->where('employee_number', $employeeNumber)
            ->first();

        if (! $employee) {
            return null;
        }

        $item = PayrollItem::query()
            ->with(['employee', 'payrollPeriod'])
            ->where('employee_id', $employee->id)
            ->whereHas('payrollPeriod', fn ($query) => $query->where('year', $year)->where('month', $month))
            ->first();

        if (! $item || $this->verificationCode($item->payrollPeriod, $item) !== $code) {
            return null;
        }

        return [
            'verification_code' => $code,
            'employee_name' => $employee->name,
            'employee_number' => $employee->employee_number,
            'hrd_name' => $this->slipConfig->hrdName(),
            'hrd_title' => $this->slipConfig->hrdTitle(),
            'period_name' => $item->payrollPeriod->name,
            'hash' => $hash,
        ];
    }

    private function scanText(string $code, string $employeeName, string $employeeNumber, string $hrdName): string
    {
        return implode("\n", [
            __('pages.payroll_slip.scan_heading'),
            __('pages.payroll_slip.scan_hrd', ['name' => $hrdName]),
            __('pages.payroll_slip.scan_employee', ['name' => $employeeName]),
            __('pages.payroll_slip.scan_employee_number', ['number' => $employeeNumber]),
            __('pages.payroll_slip.scan_slip_number', ['number' => $code]),
        ]);
    }

    private function verificationCode(PayrollPeriod $period, PayrollItem $item): string
    {
        $employeeNumber = $item->employee->employee_number ?? (string) $item->employee_id;
        $payload = implode('|', [
            $period->id,
            $item->id,
            $period->year,
            $period->month,
            $item->employee_id,
            config('app.key'),
        ]);
        $hash = strtoupper(substr(hash('sha256', $payload), 0, 8));

        return sprintf(
            'SLIP-%s%s-%s-%s',
            $period->year,
            str_pad((string) $period->month, 2, '0', STR_PAD_LEFT),
            $employeeNumber,
            $hash,
        );
    }
}

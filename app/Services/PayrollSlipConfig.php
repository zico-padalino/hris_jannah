<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PayrollSlipConfig
{
    /** @return array{hrd_name: string, hrd_title: string, signature_path: string} */
    public function all(): array
    {
        return [
            'hrd_name' => (string) SystemSetting::getValue('payroll_slip_hrd_name', 'Kepala Bagian HRD'),
            'hrd_title' => (string) SystemSetting::getValue('payroll_slip_hrd_title', 'HRD RS JANNAH'),
            'signature_path' => (string) SystemSetting::getValue('payroll_slip_hrd_signature', ''),
        ];
    }

    public function hrdName(): string
    {
        return $this->all()['hrd_name'];
    }

    public function hrdTitle(): string
    {
        return $this->all()['hrd_title'];
    }

    public function hasSignature(): bool
    {
        $path = $this->all()['signature_path'];

        return $path !== '' && Storage::disk('public')->exists($path);
    }

    public function signatureUrl(): ?string
    {
        if (! $this->hasSignature()) {
            return null;
        }

        $updatedAt = SystemSetting::query()
            ->where('key', 'payroll_slip_hrd_signature')
            ->value('updated_at');

        return route('payroll-slip.signature', [
            'v' => $updatedAt ? strtotime((string) $updatedAt) : 0,
        ], false);
    }

    /** @param array{hrd_name: string, hrd_title: string} $data */
    public function save(array $data, ?UploadedFile $signature = null, bool $removeSignature = false): void
    {
        SystemSetting::setValue('payroll_slip_hrd_name', trim($data['hrd_name']), 'Nama penandatangan slip gaji HRD');
        SystemSetting::setValue('payroll_slip_hrd_title', trim($data['hrd_title']), 'Jabatan penandatangan slip gaji HRD');

        if ($removeSignature) {
            $this->deleteSignature($this->all()['signature_path']);
            SystemSetting::setValue('payroll_slip_hrd_signature', '', 'Gambar tanda tangan HRD pada slip gaji');
        } elseif ($signature !== null) {
            $this->deleteSignature($this->all()['signature_path']);
            $extension = strtolower($signature->getClientOriginalExtension() ?: 'png');
            $path = $signature->storeAs('payroll', 'hrd-signature.'.$extension, 'public');
            SystemSetting::setValue('payroll_slip_hrd_signature', $path, 'Gambar tanda tangan HRD pada slip gaji');
        }
    }

    private function deleteSignature(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}

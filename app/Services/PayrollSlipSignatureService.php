<?php

namespace App\Services;

use App\Enums\PayrollSlipSignatureStatus;
use App\Models\PayrollItem;
use App\Models\PayrollSlipSignature;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class PayrollSlipSignatureService
{
    public function forItem(PayrollItem $item): ?PayrollSlipSignature
    {
        return PayrollSlipSignature::query()
            ->where('payroll_item_id', $item->id)
            ->first();
    }

    public function isApproved(PayrollItem $item): bool
    {
        return $this->forItem($item)?->status === PayrollSlipSignatureStatus::Approved;
    }

    public function request(PayrollItem $item, User $user): PayrollSlipSignature
    {
        $existing = $this->forItem($item);

        if ($existing !== null) {
            if ($existing->status === PayrollSlipSignatureStatus::Approved) {
                throw ValidationException::withMessages([
                    'signature' => __('pages.payroll_slip.signature_already_approved'),
                ]);
            }

            if ($existing->status === PayrollSlipSignatureStatus::Pending) {
                return $existing;
            }
        }

        return PayrollSlipSignature::query()->create([
            'payroll_item_id' => $item->id,
            'status' => PayrollSlipSignatureStatus::Pending,
            'requested_by' => $user->id,
        ]);
    }

    public function approve(PayrollItem $item, User $user): PayrollSlipSignature
    {
        $signature = $this->forItem($item);

        if ($signature === null || $signature->status !== PayrollSlipSignatureStatus::Pending) {
            throw ValidationException::withMessages([
                'signature' => __('pages.payroll_slip.signature_not_pending'),
            ]);
        }

        $signature->update([
            'status' => PayrollSlipSignatureStatus::Approved,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return $signature->fresh();
    }
}

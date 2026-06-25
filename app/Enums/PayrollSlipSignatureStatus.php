<?php

namespace App\Enums;

enum PayrollSlipSignatureStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';

    public function label(): string
    {
        return __('enums.payroll_slip_signature_status.'.$this->value);
    }
}

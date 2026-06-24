<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employeeId = $this->route('employee')?->id;

        return [
            'branch_id' => ['sometimes', 'exists:branches,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'employee_number' => ['sometimes', 'string', 'max:50', Rule::unique('employees', 'employee_number')->ignore($employeeId)],
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'employment_status' => ['sometimes', 'string', 'in:permanent,contract,honorary'],
            'base_salary' => ['sometimes', 'numeric', 'min:0'],
            'join_date' => ['nullable', 'date'],
            'contract_start_date' => ['nullable', 'date'],
            'contract_end_date' => ['nullable', 'date', 'after_or_equal:contract_start_date'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}

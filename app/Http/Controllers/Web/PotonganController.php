<?php

namespace App\Http\Controllers\Web;

use App\Enums\Permission;
use App\Services\PayrollDeductionConfig;
use App\Services\PayrollSlipConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PotonganController extends WebController
{
    public function __construct(
        private readonly PayrollDeductionConfig $deductionConfig,
        private readonly PayrollSlipConfig $slipConfig,
    ) {}

    public function index(Request $request): View
    {
        $this->authorizePermission($request, Permission::PayrollManage);

        $settings = $this->deductionConfig->all();
        $slipSettings = $this->slipConfig->all();
        $hasSlipSignature = $this->slipConfig->hasSignature();
        $slipSignatureUrl = $this->slipConfig->signatureUrl();

        return view('potongan.index', compact('settings', 'slipSettings', 'hasSlipSignature', 'slipSignatureUrl'));
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizePermission($request, Permission::PayrollManage);

        $request->merge([
            'attendance_amount' => $this->normalizeRupiah($request->input('attendance_amount')),
            'bpjs_kes_salary_cap' => $this->normalizeRupiah($request->input('bpjs_kes_salary_cap')),
            'bpjs_tk_jp_salary_cap' => $this->normalizeRupiah($request->input('bpjs_tk_jp_salary_cap')),
        ]);

        $data = $request->validate([
            'attendance_amount' => ['required', 'numeric', 'min:0'],
            'pph21_enabled' => ['nullable', 'boolean'],
            'pph21_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'bpjs_kes_enabled' => ['nullable', 'boolean'],
            'bpjs_kes_employee_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'bpjs_kes_employer_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'bpjs_kes_salary_cap' => ['required', 'numeric', 'min:0'],
            'bpjs_tk_enabled' => ['nullable', 'boolean'],
            'bpjs_tk_jht_employee_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'bpjs_tk_jht_employer_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'bpjs_tk_jp_employee_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'bpjs_tk_jp_employer_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'bpjs_tk_jkm_employer_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'bpjs_tk_jkk_employer_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'bpjs_tk_jp_salary_cap' => ['required', 'numeric', 'min:0'],
        ]);

        $this->deductionConfig->save([
            ...$data,
            'pph21_enabled' => $request->boolean('pph21_enabled'),
            'bpjs_kes_enabled' => $request->boolean('bpjs_kes_enabled'),
            'bpjs_tk_enabled' => $request->boolean('bpjs_tk_enabled'),
        ]);

        $slipData = $request->validate([
            'hrd_name' => ['required', 'string', 'max:120'],
            'hrd_title' => ['required', 'string', 'max:120'],
            'hrd_signature' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_hrd_signature' => ['nullable', 'boolean'],
        ]);

        $this->slipConfig->save(
            [
                'hrd_name' => $slipData['hrd_name'],
                'hrd_title' => $slipData['hrd_title'],
            ],
            $request->file('hrd_signature'),
            $request->boolean('remove_hrd_signature'),
        );

        return redirect()->route('potongan.index')->with('success', __('pages.potongan.saved'));
    }

    private function normalizeRupiah(mixed $value): string
    {
        return preg_replace('/\D/', '', (string) $value) ?: '0';
    }
}

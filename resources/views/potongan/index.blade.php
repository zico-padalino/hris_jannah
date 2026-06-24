@extends('layouts.app')

@section('title', __('pages.potongan.title'))
@section('subtitle', __('pages.potongan.subtitle'))

@section('content')
    <form method="POST" action="{{ route('potongan.update') }}" class="mx-auto max-w-4xl space-y-6">
        @csrf
        @method('PUT')

        <section class="app-card p-5 sm:p-6">
            <h2 class="text-lg font-bold text-slate-900">{{ __('pages.potongan.attendance_title') }}</h2>
            <p class="mt-1 text-sm app-muted-text">{{ __('pages.potongan.attendance_hint') }}</p>

            @include('partials.rupiah-input', [
                'name' => 'attendance_amount',
                'label' => __('pages.potongan.attendance_amount'),
                'value' => old('attendance_amount', $settings['attendance_amount']),
                'required' => true,
                'wrapperClass' => 'mt-4 max-w-md',
            ])
        </section>

        <section class="app-card p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">{{ __('pages.potongan.pph21_title') }}</h2>
                    <p class="mt-1 text-sm app-muted-text">{{ __('pages.potongan.pph21_hint') }}</p>
                </div>
                <label class="inline-flex items-center gap-2 text-sm font-semibold">
                    <input type="checkbox" name="pph21_enabled" value="1" @checked(old('pph21_enabled', $settings['pph21_enabled'])) class="h-4 w-4 rounded">
                    {{ __('pages.potongan.enabled') }}
                </label>
            </div>

            <label class="mt-4 block max-w-xs">
                <span class="form-label">{{ __('pages.potongan.pph21_rate') }}</span>
                <div class="flex items-center gap-2">
                    <input
                        type="number"
                        name="pph21_rate"
                        value="{{ old('pph21_rate', $settings['pph21_rate']) }}"
                        min="0"
                        max="100"
                        step="0.01"
                        required
                        class="w-full"
                    >
                    <span class="text-sm font-semibold app-muted-text">%</span>
                </div>
            </label>
        </section>

        <section class="app-card p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">{{ __('pages.potongan.bpjs_kes_title') }}</h2>
                    <p class="mt-1 text-sm app-muted-text">{{ __('pages.potongan.bpjs_kes_hint') }}</p>
                </div>
                <label class="inline-flex items-center gap-2 text-sm font-semibold">
                    <input type="checkbox" name="bpjs_kes_enabled" value="1" @checked(old('bpjs_kes_enabled', $settings['bpjs_kes_enabled'])) class="h-4 w-4 rounded">
                    {{ __('pages.potongan.enabled') }}
                </label>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <label class="block min-w-0">
                    <span class="form-label">{{ __('pages.potongan.employee_rate') }}</span>
                    <input type="number" name="bpjs_kes_employee_rate" value="{{ old('bpjs_kes_employee_rate', $settings['bpjs_kes_employee_rate']) }}" min="0" max="100" step="0.01" required class="w-full">
                </label>
                <label class="block min-w-0">
                    <span class="form-label">{{ __('pages.potongan.employer_rate') }}</span>
                    <input type="number" name="bpjs_kes_employer_rate" value="{{ old('bpjs_kes_employer_rate', $settings['bpjs_kes_employer_rate']) }}" min="0" max="100" step="0.01" required class="w-full">
                </label>
                @include('partials.rupiah-input', [
                    'name' => 'bpjs_kes_salary_cap',
                    'label' => __('pages.potongan.salary_cap'),
                    'value' => old('bpjs_kes_salary_cap', $settings['bpjs_kes_salary_cap']),
                    'required' => true,
                    'wrapperClass' => 'min-w-0 sm:col-span-2',
                ])
            </div>
        </section>

        <section class="app-card p-5 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">{{ __('pages.potongan.bpjs_tk_title') }}</h2>
                    <p class="mt-1 text-sm app-muted-text">{{ __('pages.potongan.bpjs_tk_hint') }}</p>
                </div>
                <label class="inline-flex items-center gap-2 text-sm font-semibold">
                    <input type="checkbox" name="bpjs_tk_enabled" value="1" @checked(old('bpjs_tk_enabled', $settings['bpjs_tk_enabled'])) class="h-4 w-4 rounded">
                    {{ __('pages.potongan.enabled') }}
                </label>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <label class="block min-w-0">
                    <span class="form-label">JHT {{ __('pages.potongan.employee_rate') }}</span>
                    <input type="number" name="bpjs_tk_jht_employee_rate" value="{{ old('bpjs_tk_jht_employee_rate', $settings['bpjs_tk_jht_employee_rate']) }}" min="0" max="100" step="0.01" required class="w-full">
                </label>
                <label class="block min-w-0">
                    <span class="form-label">JHT {{ __('pages.potongan.employer_rate') }}</span>
                    <input type="number" name="bpjs_tk_jht_employer_rate" value="{{ old('bpjs_tk_jht_employer_rate', $settings['bpjs_tk_jht_employer_rate']) }}" min="0" max="100" step="0.01" required class="w-full">
                </label>
                <label class="block min-w-0">
                    <span class="form-label">JP {{ __('pages.potongan.employee_rate') }}</span>
                    <input type="number" name="bpjs_tk_jp_employee_rate" value="{{ old('bpjs_tk_jp_employee_rate', $settings['bpjs_tk_jp_employee_rate']) }}" min="0" max="100" step="0.01" required class="w-full">
                </label>
                <label class="block min-w-0">
                    <span class="form-label">JP {{ __('pages.potongan.employer_rate') }}</span>
                    <input type="number" name="bpjs_tk_jp_employer_rate" value="{{ old('bpjs_tk_jp_employer_rate', $settings['bpjs_tk_jp_employer_rate']) }}" min="0" max="100" step="0.01" required class="w-full">
                </label>
                <label class="block min-w-0">
                    <span class="form-label">JKM {{ __('pages.potongan.employer_rate') }}</span>
                    <input type="number" name="bpjs_tk_jkm_employer_rate" value="{{ old('bpjs_tk_jkm_employer_rate', $settings['bpjs_tk_jkm_employer_rate']) }}" min="0" max="100" step="0.01" required class="w-full">
                </label>
                <label class="block min-w-0">
                    <span class="form-label">JKK {{ __('pages.potongan.employer_rate') }}</span>
                    <input type="number" name="bpjs_tk_jkk_employer_rate" value="{{ old('bpjs_tk_jkk_employer_rate', $settings['bpjs_tk_jkk_employer_rate']) }}" min="0" max="100" step="0.01" required class="w-full">
                </label>
                @include('partials.rupiah-input', [
                    'name' => 'bpjs_tk_jp_salary_cap',
                    'label' => __('pages.potongan.jp_salary_cap'),
                    'value' => old('bpjs_tk_jp_salary_cap', $settings['bpjs_tk_jp_salary_cap']),
                    'required' => true,
                    'wrapperClass' => 'min-w-0 sm:col-span-2',
                ])
            </div>
            <p class="mt-3 text-xs app-muted-text">{{ __('pages.potongan.bpjs_tk_employer_note') }}</p>
        </section>

        <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
            <button type="submit" class="btn-primary w-full sm:w-auto">{{ __('pages.potongan.save') }}</button>
        </div>
    </form>
@endsection

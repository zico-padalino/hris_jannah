@extends('layouts.app')
@section('title', __('pages.leave.create_title'))
@section('content')
<div class="mx-auto max-w-xl rounded-xl border bg-white p-6 shadow-sm">
    <form method="POST" action="{{ route('leaves.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <label class="block text-sm">
            <span class="mb-1 block font-medium">{{ __('leave.request_type') }}</span>
            <select name="type" id="leave-type" required class="w-full rounded-lg border px-3 py-2">
                <option value="annual" @selected(old('type') === 'annual')>{{ \App\Enums\LeaveType::Annual->label() }}</option>
                <option value="sick" @selected(old('type') === 'sick')>{{ \App\Enums\LeaveType::Sick->label() }}</option>
                <option value="permission" @selected(old('type') === 'permission')>{{ \App\Enums\LeaveType::Permission->label() }}</option>
                <option value="overtime" @selected(old('type') === 'overtime')>{{ \App\Enums\LeaveType::Overtime->label() }}</option>
            </select>
        </label>

        <label class="block text-sm">
            <span class="mb-1 block font-medium" id="date-label-start">Tanggal Mulai</span>
            <input name="start_date" type="date" value="{{ old('start_date') }}" required class="w-full rounded-lg border px-3 py-2">
        </label>

        <label class="block text-sm">
            <span class="mb-1 block font-medium" id="date-label-end">Tanggal Selesai</span>
            <input name="end_date" type="date" value="{{ old('end_date') }}" required class="w-full rounded-lg border px-3 py-2">
        </label>

        <label class="block text-sm">
            <span class="mb-1 block font-medium">{{ __('app.reason') }}</span>
            <textarea name="reason" rows="4" placeholder="{{ __('app.reason') }}" required class="w-full rounded-lg border px-3 py-2">{{ old('reason') }}</textarea>
        </label>

        <label class="block text-sm">
            <span class="mb-1 block font-medium">{{ __('leave.upload_proof') }}</span>
            <input
                name="proof"
                id="leave-proof"
                type="file"
                accept=".jpg,.jpeg,.png,.pdf"
                class="w-full rounded-lg border px-3 py-2 file:mr-3 file:rounded file:border-0 file:bg-teal-50 file:px-3 file:py-1 file:text-sm file:text-teal-800"
            >
            <p id="proof-hint" class="mt-1 text-xs text-slate-500">{{ __('leave.proof_hint') }}</p>
        </label>

        <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">
            {{ __('leave.submit') }}
        </button>
    </form>
</div>

@push('scripts')
<script>
    const typeSelect = document.getElementById('leave-type');
    const proofInput = document.getElementById('leave-proof');

    function syncProofRequired() {
        const type = typeSelect.value;
        const required = ['sick', 'permission'].includes(type);
        proofInput.required = required;

        const isOvertime = type === 'overtime';
        document.getElementById('date-label-start').textContent = isOvertime ? @json(__('leave.overtime_date')) : @json(__('leave.start_date'));
        document.getElementById('date-label-end').textContent = isOvertime ? @json(__('leave.overtime_date_same')) : @json(__('leave.end_date'));
    }

    typeSelect.addEventListener('change', syncProofRequired);
    syncProofRequired();
</script>
@endpush
@endsection

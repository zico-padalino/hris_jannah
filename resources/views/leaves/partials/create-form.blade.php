@php
    use App\Enums\LeaveType;

    $category = $category ?? 'cuti';
    $categoryTypes = LeaveType::forApprovalCategory($category);
    $defaultType = old('type', $categoryTypes[0]->value ?? 'annual');
@endphp

<div class="mx-auto max-w-xl rounded-xl border bg-white p-6 shadow-sm">
    <form method="POST" action="{{ route('leaves.store', ['category' => $category]) }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <input type="hidden" name="category" value="{{ $category }}">

        @if(count($categoryTypes) > 1)
            <label class="block text-sm">
                <span class="mb-1 block font-medium">{{ __('leave.request_type') }}</span>
                <select name="type" id="leave-type" required class="w-full rounded-lg border px-3 py-2">
                    @foreach($categoryTypes as $type)
                        <option value="{{ $type->value }}" @selected($defaultType === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </label>
        @else
            <input type="hidden" name="type" id="leave-type" value="{{ $categoryTypes[0]->value }}">
            <p class="text-sm text-slate-600">
                <span class="font-medium">{{ __('leave.request_type') }}:</span>
                {{ $categoryTypes[0]->label() }}
            </p>
        @endif

        <label class="block text-sm">
            <span class="mb-1 block font-medium" id="date-label-start">{{ __('leave.start_date') }}</span>
            <input name="start_date" type="date" value="{{ old('start_date') }}" required class="w-full rounded-lg border px-3 py-2">
        </label>

        <label class="block text-sm">
            <span class="mb-1 block font-medium" id="date-label-end">{{ __('leave.end_date') }}</span>
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

        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">
                {{ __('leave.submit') }}
            </button>
            <a href="{{ route('leaves.index', ['category' => $category]) }}" class="rounded-lg border px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                {{ __('nav.leave_riwayat') }}
            </a>
        </div>
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

    if (typeSelect && typeSelect.tagName === 'SELECT') {
        typeSelect.addEventListener('change', syncProofRequired);
    }

    syncProofRequired();
</script>
@endpush

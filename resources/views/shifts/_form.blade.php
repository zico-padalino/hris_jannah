@php
    $shift = $shift ?? null;
    $selectedDays = old('work_days', $shift?->resolvedWorkDays() ?? \App\Models\Shift::DEFAULT_WORK_DAYS);
    $startValue = old('start_time', $shift ? $shift->formattedStartTime() : '07:00');
    $endValue = old('end_time', $shift ? $shift->formattedEndTime() : '15:00');
    $toleranceValue = old('late_tolerance_minutes', $shift?->late_tolerance_minutes ?? 15);
@endphp

<div class="space-y-6">
    <section class="rounded-xl border border-slate-200 bg-slate-50/80 p-4">
        <h3 class="mb-3 text-sm font-semibold text-slate-800">Informasi Jadwal</h3>
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block text-sm sm:col-span-2">
                <span class="mb-1.5 block font-medium text-slate-700">Cabang</span>
                <select name="branch_id" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
                    <option value="">Semua Cabang (default)</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(old('branch_id', $shift?->branch_id) == $branch->id)>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="block text-sm">
                <span class="mb-1.5 block font-medium text-slate-700">Kode Jadwal</span>
                <input name="code" value="{{ old('code', $shift?->code) }}" placeholder="PAGI" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 uppercase focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
            </label>

            <label class="block text-sm">
                <span class="mb-1.5 block font-medium text-slate-700">Nama Jadwal</span>
                <input name="name" value="{{ old('name', $shift?->name) }}" placeholder="Shift Pagi RS" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
            </label>
        </div>
    </section>

    <section class="rounded-xl border border-teal-200 bg-gradient-to-br from-teal-50 to-white p-4">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h3 class="text-sm font-semibold text-teal-900">Jam Kerja</h3>
            <p id="shift-duration-preview" class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-teal-700 shadow-sm">
                Durasi: —
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <label class="block text-sm">
                <span class="mb-1.5 block font-medium text-slate-700">Jam Masuk</span>
                <input id="shift-start-time" name="start_time" type="time" value="{{ $startValue }}" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-lg font-semibold tabular-nums focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
            </label>

            <label class="block text-sm">
                <span class="mb-1.5 block font-medium text-slate-700">Jam Pulang</span>
                <input id="shift-end-time" name="end_time" type="time" value="{{ $endValue }}" required class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-lg font-semibold tabular-nums focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-200">
            </label>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
            <button type="button" data-shift-preset="07:00,15:00" class="shift-time-preset rounded-lg border border-teal-200 bg-white px-3 py-1.5 text-xs font-medium text-teal-800 hover:bg-teal-100">Pagi (07:00–15:00)</button>
            <button type="button" data-shift-preset="08:00,16:00" class="shift-time-preset rounded-lg border border-teal-200 bg-white px-3 py-1.5 text-xs font-medium text-teal-800 hover:bg-teal-100">Siang (08:00–16:00)</button>
            <button type="button" data-shift-preset="14:00,22:00" class="shift-time-preset rounded-lg border border-teal-200 bg-white px-3 py-1.5 text-xs font-medium text-teal-800 hover:bg-teal-100">Malam (14:00–22:00)</button>
        </div>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-3">
        <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <h3 class="text-sm font-semibold text-slate-800">Hari Kerja</h3>
            <div class="flex flex-wrap gap-1.5">
                <button type="button" data-shift-days="1,2,3,4,5" class="shift-day-preset rounded-md border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-700 hover:border-teal-300 hover:bg-teal-50">Sen–Jum</button>
                <button type="button" data-shift-days="1,2,3,4,5,6" class="shift-day-preset rounded-md border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-700 hover:border-teal-300 hover:bg-teal-50">Sen–Sab</button>
                <button type="button" data-shift-days="1,2,3,4,5,6,7" class="shift-day-preset rounded-md border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-700 hover:border-teal-300 hover:bg-teal-50">Setiap Hari</button>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-2 sm:grid-cols-7">
            @foreach(\App\Models\Shift::DAY_LABELS as $dayValue => $dayLabel)
                <label
                    title="{{ $dayLabel }}"
                    class="flex cursor-pointer items-center justify-center rounded-lg border-2 border-slate-300 bg-slate-50 py-2.5 text-xs font-bold text-slate-600 transition hover:border-teal-400 has-[:checked]:border-teal-700 has-[:checked]:bg-teal-600 has-[:checked]:text-white has-[:checked]:shadow-sm"
                >
                    <input
                        type="checkbox"
                        name="work_days[]"
                        value="{{ $dayValue }}"
                        class="sr-only"
                        @checked(in_array($dayValue, $selectedDays, true))
                    >
                    {{ \App\Models\Shift::DAY_SHORT_LABELS[$dayValue] }}
                </label>
            @endforeach
        </div>
        <p id="shift-days-preview" class="mt-2.5 text-[11px] text-slate-500">Pilih minimal 1 hari kerja.</p>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-3">
        <h3 class="mb-3 text-sm font-semibold text-slate-800">Toleransi & Status</h3>

        <div class="text-sm">
            <div class="flex flex-wrap items-center gap-x-3 gap-y-2">
                <span class="text-xs font-medium text-slate-700">Toleransi Keterlambatan</span>
                <span id="shift-tolerance-value" class="rounded-md bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-800">{{ $toleranceValue }} mnt</span>
            </div>
            <input id="shift-tolerance" name="late_tolerance_minutes" type="range" min="0" max="60" step="5" value="{{ $toleranceValue }}" class="mt-2 h-1.5 w-full max-w-xs cursor-pointer accent-teal-600">
            <div class="mt-2 flex flex-wrap gap-1.5">
                @foreach([0, 5, 10, 15, 30] as $preset)
                    <button type="button" data-shift-tolerance="{{ $preset }}" class="shift-tolerance-preset rounded-md border border-slate-200 px-2 py-0.5 text-[11px] text-slate-600 hover:border-amber-300 hover:bg-amber-50">{{ $preset }} mnt</button>
                @endforeach
            </div>
        </div>

        <label class="mt-3 flex cursor-pointer items-center justify-between rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
            <div>
                <span class="block text-xs font-medium text-slate-800">Jadwal Aktif</span>
                <span class="text-[11px] text-slate-500">Nonaktifkan jika tidak dipakai</span>
            </div>
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $shift?->is_active ?? true)) class="h-4 w-4 rounded border-slate-300 text-teal-700 focus:ring-teal-600">
        </label>
    </section>
</div>

@push('scripts')
<script>
    const startInput = document.getElementById('shift-start-time');
    const endInput = document.getElementById('shift-end-time');
    const durationPreview = document.getElementById('shift-duration-preview');
    const daysPreview = document.getElementById('shift-days-preview');
    const toleranceInput = document.getElementById('shift-tolerance');
    const toleranceValue = document.getElementById('shift-tolerance-value');
    const dayCheckboxes = () => Array.from(document.querySelectorAll('input[name="work_days[]"]'));

    function parseMinutes(time) {
        const [hours, minutes] = time.split(':').map(Number);
        return (hours * 60) + minutes;
    }

    function updateDurationPreview() {
        if (!startInput.value || !endInput.value) {
            durationPreview.textContent = 'Durasi: —';
            return;
        }

        let start = parseMinutes(startInput.value);
        let end = parseMinutes(endInput.value);
        if (end <= start) end += 24 * 60;

        const total = end - start;
        const hours = Math.floor(total / 60);
        const minutes = total % 60;
        durationPreview.textContent = minutes === 0
            ? `Durasi: ${hours} jam`
            : `Durasi: ${hours} jam ${minutes} mnt`;
    }

    function updateDaysPreview() {
        const selected = dayCheckboxes().filter((input) => input.checked).length;
        daysPreview.textContent = selected === 0
            ? 'Pilih minimal 1 hari kerja.'
            : `${selected} hari kerja dipilih per minggu.`;
        daysPreview.classList.toggle('text-red-600', selected === 0);
        daysPreview.classList.toggle('text-slate-500', selected > 0);
    }

    function updateTolerancePreview() {
        toleranceValue.textContent = `${toleranceInput.value} mnt`;
    }

    startInput?.addEventListener('input', updateDurationPreview);
    endInput?.addEventListener('input', updateDurationPreview);
    toleranceInput?.addEventListener('input', updateTolerancePreview);
    dayCheckboxes().forEach((input) => input.addEventListener('change', updateDaysPreview));

    document.querySelectorAll('.shift-time-preset').forEach((button) => {
        button.addEventListener('click', () => {
            const [start, end] = button.dataset.shiftPreset.split(',');
            startInput.value = start;
            endInput.value = end;
            updateDurationPreview();
        });
    });

    document.querySelectorAll('.shift-day-preset').forEach((button) => {
        button.addEventListener('click', () => {
            const days = button.dataset.shiftDays.split(',').map(Number);
            dayCheckboxes().forEach((input) => {
                input.checked = days.includes(Number(input.value));
            });
            updateDaysPreview();
        });
    });

    document.querySelectorAll('.shift-tolerance-preset').forEach((button) => {
        button.addEventListener('click', () => {
            toleranceInput.value = button.dataset.shiftTolerance;
            updateTolerancePreview();
        });
    });

    updateDurationPreview();
    updateDaysPreview();
    updateTolerancePreview();
</script>
@endpush

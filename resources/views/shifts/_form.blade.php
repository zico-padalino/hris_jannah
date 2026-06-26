@php
    $shift = $shift ?? null;
    $selectedDays = old('work_days', $shift?->resolvedWorkDays() ?? \App\Models\Shift::DEFAULT_WORK_DAYS);
    $startValue = old('start_time', $shift ? $shift->formattedStartTime() : '07:00');
    $endValue = old('end_time', $shift ? $shift->formattedEndTime() : '15:00');
    $toleranceValue = old('late_tolerance_minutes', $shift?->late_tolerance_minutes ?? 15);
@endphp

<div class="shift-form space-y-5">
    <section class="shift-form__section">
        <h3 class="shift-form__title">Informasi Jadwal</h3>
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="shift-form__field sm:col-span-2">
                <span class="shift-form__label">Cabang</span>
                <select name="branch_id" class="w-full">
                    <option value="">Semua Cabang (default)</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(old('branch_id', $shift?->branch_id) == $branch->id)>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="shift-form__field">
                <span class="shift-form__label">Kode Jadwal</span>
                <input name="code" value="{{ old('code', $shift?->code) }}" placeholder="PAGI" required class="w-full uppercase">
            </label>

            <label class="shift-form__field">
                <span class="shift-form__label">Nama Jadwal</span>
                <input name="name" value="{{ old('name', $shift?->name) }}" placeholder="Shift Pagi RS" required class="w-full">
            </label>
        </div>
    </section>

    <section class="shift-form__section shift-form__section--highlight">
        <div class="shift-form__section-head">
            <h3 class="shift-form__title">Jam Kerja</h3>
            <p id="shift-duration-preview" class="shift-form__duration-badge">Durasi: —</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <label class="shift-form__field">
                <span class="shift-form__label">Jam Masuk</span>
                <input id="shift-start-time" name="start_time" type="time" value="{{ $startValue }}" required class="shift-form__time-input w-full">
            </label>

            <label class="shift-form__field">
                <span class="shift-form__label">Jam Pulang</span>
                <input id="shift-end-time" name="end_time" type="time" value="{{ $endValue }}" required class="shift-form__time-input w-full">
            </label>
        </div>

        <div class="shift-form__presets">
            <button type="button" data-shift-preset="07:00,15:00" class="shift-form__preset">Pagi (07:00–15:00)</button>
            <button type="button" data-shift-preset="08:00,16:00" class="shift-form__preset">Siang (08:00–16:00)</button>
            <button type="button" data-shift-preset="14:00,22:00" class="shift-form__preset">Malam (14:00–22:00)</button>
        </div>
    </section>

    <section class="shift-form__section">
        <div class="shift-form__section-head">
            <h3 class="shift-form__title">Hari Kerja</h3>
            <div class="shift-form__presets shift-form__presets--inline">
                <button type="button" data-shift-days="1,2,3,4,5" class="shift-form__preset shift-form__preset--compact">Sen–Jum</button>
                <button type="button" data-shift-days="1,2,3,4,5,6" class="shift-form__preset shift-form__preset--compact">Sen–Sab</button>
                <button type="button" data-shift-days="1,2,3,4,5,6,7" class="shift-form__preset shift-form__preset--compact">Setiap Hari</button>
            </div>
        </div>

        <div class="shift-form__days">
            @foreach(\App\Models\Shift::DAY_LABELS as $dayValue => $dayLabel)
                <label class="shift-form__day" title="{{ $dayLabel }}">
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
        <p id="shift-days-preview" class="shift-form__hint">Pilih minimal 1 hari kerja.</p>
    </section>

    <section class="shift-form__section">
        <h3 class="shift-form__title">Toleransi & Status</h3>

        <div class="shift-form__tolerance">
            <div class="shift-form__tolerance-head">
                <span class="shift-form__label">Toleransi Keterlambatan</span>
                <span id="shift-tolerance-value" class="shift-form__tolerance-value">{{ $toleranceValue }} mnt</span>
            </div>
            <input id="shift-tolerance" name="late_tolerance_minutes" type="range" min="0" max="60" step="5" value="{{ $toleranceValue }}" class="shift-form__range">
            <div class="shift-form__presets shift-form__presets--inline">
                @foreach([0, 5, 10, 15, 30] as $preset)
                    <button type="button" data-shift-tolerance="{{ $preset }}" class="shift-form__preset shift-form__preset--compact">{{ $preset }} mnt</button>
                @endforeach
            </div>
        </div>

        <label class="shift-form__toggle">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $shift?->is_active ?? true))>
            <span class="shift-form__toggle-box" aria-hidden="true"></span>
            <span>
                <span class="shift-form__toggle-title">Jadwal Aktif</span>
                <span class="shift-form__toggle-desc">Nonaktifkan jika tidak dipakai</span>
            </span>
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
        daysPreview.classList.toggle('shift-form__hint--error', selected === 0);
    }

    function updateTolerancePreview() {
        toleranceValue.textContent = `${toleranceInput.value} mnt`;
    }

    startInput?.addEventListener('input', updateDurationPreview);
    endInput?.addEventListener('input', updateDurationPreview);
    toleranceInput?.addEventListener('input', updateTolerancePreview);
    dayCheckboxes().forEach((input) => input.addEventListener('change', updateDaysPreview));

    document.querySelectorAll('[data-shift-preset]').forEach((button) => {
        button.addEventListener('click', () => {
            const [start, end] = button.dataset.shiftPreset.split(',');
            startInput.value = start;
            endInput.value = end;
            updateDurationPreview();
        });
    });

    document.querySelectorAll('[data-shift-days]').forEach((button) => {
        button.addEventListener('click', () => {
            const days = button.dataset.shiftDays.split(',').map(Number);
            dayCheckboxes().forEach((input) => {
                input.checked = days.includes(Number(input.value));
            });
            updateDaysPreview();
        });
    });

    document.querySelectorAll('[data-shift-tolerance]').forEach((button) => {
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

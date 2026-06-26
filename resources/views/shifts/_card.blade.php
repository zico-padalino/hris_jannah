@php
    $isActive = $shift->is_active;
@endphp

<article @class(['shift-card panel', 'shift-card--active' => $isActive])>
    <div class="shift-card__body">
        <div class="shift-card__head">
            <div class="min-w-0 flex-1">
                <div class="shift-card__badges">
                    <span class="shift-card__code">{{ $shift->code }}</span>
                    @include('partials.active-status-badge', ['active' => $isActive])
                </div>
                <h3 class="shift-card__title">{{ $shift->name }}</h3>
                <p class="shift-card__branch">{{ $shift->branch->name ?? 'Semua Cabang' }}</p>
            </div>
        </div>

        <div class="shift-card__hours">
            <div class="shift-card__hour">
                <p class="shift-card__hour-label">Masuk</p>
                <p class="shift-card__hour-value">{{ $shift->formattedStartTime() }}</p>
            </div>
            <div class="shift-card__duration">{{ $shift->workDurationLabel() }}</div>
            <div class="shift-card__hour">
                <p class="shift-card__hour-label">Pulang</p>
                <p class="shift-card__hour-value">{{ $shift->formattedEndTime() }}</p>
            </div>
        </div>

        <div class="shift-card__days" aria-label="Hari kerja">
            @foreach(\App\Models\Shift::DAY_SHORT_LABELS as $dayValue => $dayLabel)
                @php $dayActive = in_array($dayValue, $shift->resolvedWorkDays(), true); @endphp
                <span @class(['shift-card__day', 'shift-card__day--on' => $dayActive, 'shift-card__day--off' => ! $dayActive])>
                    {{ $dayLabel }}
                </span>
            @endforeach
        </div>

        <div class="shift-card__meta">
            <span class="shift-card__chip shift-card__chip--warning">Telat {{ $shift->late_tolerance_minutes }} mnt</span>
            <span class="shift-card__chip">{{ $shift->employees_count }} pegawai</span>
            <span class="shift-card__chip">{{ $shift->workDaysCount() }} hari/mgg</span>
        </div>

        <div class="shift-card__actions">
            @include('partials.table-actions', [
                'module' => 'shift_templates',
                'layout' => 'bar',
                'edit' => route('shifts.edit', $shift),
                'delete' => route('shifts.destroy', $shift),
                'deleteConfirm' => 'Hapus jadwal '.$shift->name.'?',
            ])
        </div>
    </div>
</article>

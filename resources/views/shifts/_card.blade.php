@php
    $isActive = $shift->is_active;
@endphp

<article @class([
    'overflow-hidden rounded-lg border bg-white shadow-sm',
    'border-slate-200' => ! $isActive,
    'border-teal-200' => $isActive,
])>
    <div class="p-3">
        <div class="mb-2 flex items-start justify-between gap-2">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-1">
                    <span class="rounded bg-teal-50 px-1.5 py-0.5 text-[10px] font-bold text-teal-700">{{ $shift->code }}</span>
                    @if($isActive)
                        <span class="rounded bg-emerald-100 px-1.5 py-0.5 text-[10px] font-medium text-emerald-700">Aktif</span>
                    @else
                        <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-600">Nonaktif</span>
                    @endif
                </div>
                <h3 class="mt-1 truncate text-sm font-semibold text-slate-900">{{ $shift->name }}</h3>
                <p class="truncate text-[11px] text-slate-500">{{ $shift->branch->name ?? 'Semua Cabang' }}</p>
            </div>
        </div>

        <div class="mb-2 flex items-center justify-between rounded-md bg-slate-50 px-2 py-1.5 text-xs">
            <div class="text-center">
                <p class="text-[10px] text-slate-400">Masuk</p>
                <p class="font-bold tabular-nums text-teal-700">{{ $shift->formattedStartTime() }}</p>
            </div>
            <div class="px-1 text-[10px] text-slate-500">{{ $shift->workDurationLabel() }}</div>
            <div class="text-center">
                <p class="text-[10px] text-slate-400">Pulang</p>
                <p class="font-bold tabular-nums text-teal-700">{{ $shift->formattedEndTime() }}</p>
            </div>
        </div>

        <div class="mb-2 grid grid-cols-7 gap-0.5">
            @foreach(\App\Models\Shift::DAY_SHORT_LABELS as $dayValue => $dayLabel)
                @php $active = in_array($dayValue, $shift->resolvedWorkDays(), true); @endphp
                <div @class([
                    'rounded py-0.5 text-center text-[9px] font-semibold',
                    'bg-teal-600 text-white' => $active,
                    'bg-slate-100 text-slate-400' => ! $active,
                ])>
                    {{ $dayLabel }}
                </div>
            @endforeach
        </div>

        <div class="mb-2 flex flex-wrap gap-1 text-[10px] text-slate-600">
            <span class="rounded bg-amber-50 px-1.5 py-0.5 text-amber-800">Telat {{ $shift->late_tolerance_minutes }} mnt</span>
            <span class="rounded bg-slate-100 px-1.5 py-0.5">{{ $shift->employees_count }} pegawai</span>
            <span class="rounded bg-slate-100 px-1.5 py-0.5">{{ $shift->workDaysCount() }} hari/mgg</span>
        </div>

        <div class="border-t border-slate-100 pt-2">
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

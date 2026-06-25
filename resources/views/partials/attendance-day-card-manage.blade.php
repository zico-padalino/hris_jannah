<div class="attendance-mobile-card panel overflow-hidden">
    <div class="border-b-2 border-slate-200 bg-slate-50 px-4 py-3">
        <p class="text-lg font-extrabold text-slate-900">{{ $dayGroup->employee->name }}</p>
        <p class="mt-0.5 text-sm font-semibold text-slate-600">{{ $dayGroup->branchLabel() }}</p>
        <p class="mt-2 text-base font-bold text-campfire-1">
            {{ $dayGroup->date->format('d/m/Y') }}
            <span class="font-semibold text-slate-600">· {{ $dayGroup->date->locale(app()->getLocale())->translatedFormat('l') }}</span>
        </p>
    </div>

    <div class="space-y-3 p-4">
        @foreach($dayGroup->displayRecords() as $record)
            <div class="rounded-lg border-2 border-slate-200 bg-white p-3">
                <div class="mb-3">
                    @include('partials.attendance-time-entry', ['attendance' => $record])
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <p class="mb-1 text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('attendance.verification') }}</p>
                        @include('partials.attendance-day-verification', ['attendance' => $record, 'large' => true])
                    </div>
                    <div>
                        <p class="mb-1 text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('app.status') }}</p>
                        @include('partials.attendance-status-entry', ['attendance' => $record])
                    </div>
                </div>
                <div class="mt-3 border-t border-slate-100 pt-3">
                    <p class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('pages.attendance_manage.update_status') }}</p>
                    @include('partials.attendance-status-update-form', ['attendance' => $record])
                </div>
            </div>
        @endforeach
    </div>

    <div class="border-t-2 border-slate-200 bg-slate-50 px-4 py-3 text-center">
        <p class="mb-1 text-xs font-bold uppercase tracking-wide text-slate-500">{{ __('attendance.deduction') }}</p>
        @if($dayGroup->totalDeduction() > 0)
            <span class="deduction-amount">Rp {{ number_format($dayGroup->totalDeduction(), 0, ',', '.') }}</span>
        @else
            <span class="empty-dash">—</span>
        @endif
    </div>
</div>

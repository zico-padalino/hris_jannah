<div class="attendance-mobile-card panel overflow-hidden">
    <div class="attendance-mobile-card__head">
        <div class="attendance-mobile-card__head-main min-w-0">
            <p class="attendance-mobile-card__name">{{ $dayGroup->employee->name }}</p>
            <p class="attendance-mobile-card__meta">
                {{ $dayGroup->branchLabel() }}
                <span aria-hidden="true">·</span>
                {{ $dayGroup->date->format('d/m/Y') }}
                <span aria-hidden="true">·</span>
                {{ $dayGroup->date->locale(app()->getLocale())->translatedFormat('l') }}
            </p>
        </div>
        <div class="attendance-mobile-card__deduction shrink-0">
            <span class="attendance-mobile-card__deduction-label">{{ __('attendance.deduction') }}</span>
            @if($dayGroup->totalDeduction() > 0)
                <span class="attendance-mobile-card__deduction-value deduction-amount">Rp {{ number_format($dayGroup->totalDeduction(), 0, ',', '.') }}</span>
            @else
                <span class="attendance-mobile-card__deduction-value empty-dash">—</span>
            @endif
        </div>
    </div>

    <div class="attendance-mobile-card__body">
        @foreach($dayGroup->displayRecords() as $record)
            <div class="attendance-mobile-card__record">
                <div class="attendance-mobile-card__time">
                    @include('partials.attendance-time-entry', ['attendance' => $record])
                </div>
                <div class="attendance-mobile-card__details">
                    <div class="attendance-mobile-card__detail">
                        <span class="attendance-mobile-card__detail-label">{{ __('attendance.verification') }}</span>
                        @include('partials.attendance-day-verification', ['attendance' => $record, 'large' => false])
                    </div>
                    <div class="attendance-mobile-card__detail">
                        <span class="attendance-mobile-card__detail-label">{{ __('app.status') }}</span>
                        @include('partials.attendance-status-entry', ['attendance' => $record])
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

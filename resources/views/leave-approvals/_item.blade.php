<article class="leave-approval-item panel">
    <div class="leave-approval-item__head">
        <div class="min-w-0">
            <h3 class="leave-approval-item__name">{{ $leave->employee->name }}</h3>
            <p class="leave-approval-item__meta">
                {{ $leave->branch->name }}
                <span>{{ $leave->type->label() }}</span>
            </p>
        </div>
        <span class="leave-approval-status leave-approval-status--{{ $leave->status->value }}">{{ $leave->status->label() }}</span>
    </div>

    <p class="leave-approval-item__period">
        {{ $leave->start_date->format('d/m/Y') }} – {{ $leave->end_date->format('d/m/Y') }}
    </p>

    <p class="leave-approval-item__reason">{{ $leave->reason }}</p>

    @if($leave->admin_notes)
        <p class="leave-approval-item__note">{{ __('app.admin_notes') }}: {{ $leave->admin_notes }}</p>
    @endif

    <div class="leave-approval-item__foot">
        <div class="leave-approval-item__proof">
            @include('partials.leave-proof-link', ['leave' => $leave])
        </div>
        @if($leave->approver)
            <span class="leave-approval-item__processed">{{ $leave->approver->name }} · {{ $leave->approved_at?->format('d/m H:i') }}</span>
        @endif
    </div>

    @if($leave->status->value === 'pending')
        <div class="leave-approval-actions">
            <form method="POST" action="{{ route('leave-approvals.approve', $leave) }}" class="leave-approval-actions__form">
                @csrf
                <input type="text" name="admin_notes" placeholder="{{ __('leave.approval_note_optional') }}" class="leave-approval-actions__input w-full">
                <button type="submit" class="btn-primary leave-approval-actions__btn">{{ __('app.approve') }}</button>
            </form>
            <form method="POST" action="{{ route('leave-approvals.reject', $leave) }}" class="leave-approval-actions__form">
                @csrf
                <input type="text" name="admin_notes" placeholder="{{ __('leave.approval_reject_reason') }}" class="leave-approval-actions__input w-full">
                <button type="submit" class="btn-secondary leave-approval-actions__btn leave-approval-actions__btn--reject">{{ __('app.reject') }}</button>
            </form>
        </div>
    @endif
</article>

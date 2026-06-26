<article class="activity-log-item panel">
    <div class="activity-log-item__head">
        <span class="{{ $log->action->badgeClass() }}">{{ $log->action->label() }}</span>
        <span class="activity-log-item__time">
            <span class="activity-log-item__id">#{{ $log->id }}</span>
            {{ $log->created_at?->format('d/m/Y H:i') }}
        </span>
    </div>

    <p class="activity-log-item__user">
        {{ $log->user_name ?? __('pages.activity_logs.unknown_user') }}
        @if($log->user_email)
            <span class="activity-log-item__email">{{ $log->user_email }}</span>
        @endif
    </p>

    <p class="activity-log-item__meta">
        @if($log->user_role)
            <span>{{ __('enums.user_role.'.$log->user_role) }}</span>
        @endif
        @if($log->branch?->name)
            <span>{{ $log->branch->name }}</span>
        @endif
        @if($log->module)
            <span>{{ $log->module }}</span>
        @endif
    </p>

    @if($log->subjectDisplay() !== '—')
        <p class="activity-log-item__subject">{{ $log->subjectDisplay() }}</p>
    @endif

    @if($log->description)
        <p class="activity-log-item__desc">{{ $log->description }}</p>
    @endif

    @if($log->ip_address)
        <p class="activity-log-item__ip">{{ $log->ip_address }}</p>
    @endif
</article>

@extends('layouts.app')

@section('title', __('pages.maintenance.title'))

@section('content')
@php
    $moduleLabel = $module ? __($module->navLabelKey()) : __('pages.maintenance.unknown_module');
    $displayMessage = $message ?? __('pages.maintenance.default_message', ['module' => $moduleLabel]);
@endphp

<div class="maintenance-page mx-auto flex min-h-[calc(100vh-12rem)] max-w-3xl flex-col items-center justify-center py-6 sm:py-10">
    <div class="maintenance-card relative w-full overflow-hidden rounded-3xl border shadow-xl" style="background-color: var(--app-surface); border-color: var(--app-border); box-shadow: 0 24px 48px -12px var(--app-shadow);">
        <div class="maintenance-card-accent absolute inset-x-0 top-0 h-1.5 bg-gradient-to-r from-amber-400 via-orange-500 to-amber-400"></div>

        <div class="maintenance-card-glow pointer-events-none absolute -right-16 -top-16 h-48 w-48 rounded-full bg-amber-400/10 blur-3xl"></div>
        <div class="maintenance-card-glow pointer-events-none absolute -bottom-20 -left-16 h-56 w-56 rounded-full bg-teal-500/10 blur-3xl"></div>

        <div class="maintenance-content relative mx-auto w-full max-w-xl px-6 py-10 sm:px-10 sm:py-12">
            <div class="flex flex-col items-center text-center">
                <div class="maintenance-icon-wrap relative mx-auto flex h-20 w-20 items-center justify-center">
                    <span class="maintenance-icon-ring pointer-events-none absolute inset-0 rounded-full" aria-hidden="true"></span>
                    <div class="maintenance-icon relative flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-100 to-orange-50 text-amber-600 shadow-lg ring-1 ring-amber-200/80 dark:from-amber-950 dark:to-orange-950 dark:text-amber-300 dark:ring-amber-800/60">
                        <svg class="maintenance-icon-gear h-9 w-9" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0Z" />
                        </svg>
                        <svg class="maintenance-icon-wrench absolute -bottom-1 -right-1 h-6 w-6 rounded-full bg-white p-0.5 text-orange-500 shadow-md ring-2 ring-amber-100 dark:bg-slate-800 dark:ring-amber-900" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03a2.652 2.652 0 1 0-3.819-3.819l-3.03 2.496m5.353 4.353-4.353-4.353m11.364 0a9 9 0 1 1-12.728 0 9 9 0 0 1 12.728 0Z" />
                        </svg>
                    </div>
                </div>

                <span class="maintenance-badge mt-8 inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-bold uppercase leading-none tracking-wider">
                    <span class="maintenance-badge-dot h-2 w-2 shrink-0 rounded-full" aria-hidden="true"></span>
                    {{ __('pages.maintenance.status_badge') }}
                </span>

                <h2 class="mt-5 text-2xl font-extrabold tracking-tight sm:text-3xl" style="color: var(--app-text);">
                    {{ __('pages.maintenance.heading') }}
                </h2>

                @if($module)
                    <p class="mt-2 text-lg font-semibold leading-snug" style="color: var(--app-primary);">
                        {{ $moduleLabel }}
                    </p>
                @endif
            </div>

            <div class="maintenance-panel mt-8 w-full rounded-2xl border px-5 py-4 text-center" style="background-color: var(--app-surface-muted); border-color: var(--app-border);">
                <p class="text-base leading-relaxed" style="color: var(--app-text-muted);">
                    {{ $displayMessage }}
                </p>
            </div>

            @if($module?->showsAttendanceFingerprintNotice())
                <div class="maintenance-attendance-notice app-notice mt-6 w-full text-left">
                    <p>
                        <strong>{{ __('attendance.fingerprint_notice_lead') }}</strong>
                        {{ __('attendance.fingerprint_notice_body') }}
                    </p>
                </div>
            @endif

            <div class="maintenance-actions mt-8">
                <a href="{{ route('dashboard') }}" class="btn-primary maintenance-btn inline-flex items-center justify-center gap-2">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    {{ __('pages.maintenance.back_dashboard') }}
                </a>
                <button type="button" onclick="window.history.back()" class="btn-secondary maintenance-btn inline-flex items-center justify-center gap-2">
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    {{ __('pages.maintenance.go_back') }}
                </button>
            </div>

            <div class="maintenance-panel mt-8 w-full rounded-2xl border p-5 sm:p-6" style="background-color: var(--app-surface-muted); border-color: var(--app-border);">
                <p class="text-sm font-bold" style="color: var(--app-text);">
                    {{ __('pages.maintenance.tip_title') }}
                </p>
                <ul class="mt-4 space-y-3">
                    <li class="flex items-start gap-3 text-left text-sm leading-relaxed" style="color: var(--app-text-muted);">
                        <span class="maintenance-tip-num mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold">1</span>
                        <span>{{ __('pages.maintenance.tip_dashboard') }}</span>
                    </li>
                    <li class="flex items-start gap-3 text-left text-sm leading-relaxed" style="color: var(--app-text-muted);">
                        <span class="maintenance-tip-num mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold">2</span>
                        <span>{{ __('pages.maintenance.tip_other') }}</span>
                    </li>
                    <li class="flex items-start gap-3 text-left text-sm leading-relaxed" style="color: var(--app-text-muted);">
                        <span class="maintenance-tip-num mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold">3</span>
                        <span>{{ __('pages.maintenance.tip_contact') }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <p class="maintenance-footer mt-6 max-w-xl px-4 text-center text-xs font-medium leading-relaxed" style="color: var(--app-text-muted);">
        {{ $appBranding->name() }} · {{ __('pages.maintenance.footer_note') }}
    </p>
</div>
@endsection

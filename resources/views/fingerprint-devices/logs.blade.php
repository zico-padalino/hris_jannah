@extends('layouts.app')

@section('title', 'Log Fingerprint')
@section('subtitle', $fingerprintDevice->name)

@section('content')
<div class="fingerprint-log-page space-y-3">
    <div class="fingerprint-log-page__top">
        <a href="{{ route('fingerprint-devices.edit', $fingerprintDevice) }}" class="payroll-deduction-back fingerprint-device-edit__back">
            <span class="payroll-deduction-back__icon" aria-hidden="true">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </span>
            <span>Kembali</span>
        </a>

        <div class="fingerprint-log-page__meta panel">
            <span class="fingerprint-log-page__device">{{ $fingerprintDevice->serial_number }}</span>
            <span class="fingerprint-log-page__count">{{ number_format($fingerprintDevice->logs_count) }} log</span>
        </div>
    </div>

    <div class="fingerprint-log-section panel-table">
        <div class="fingerprint-log-list lg:hidden">
            @forelse($logs as $log)
                @include('fingerprint-devices._log-item', ['log' => $log])
            @empty
                <div class="fingerprint-log-empty">
                    <p class="fingerprint-log-empty__text">Belum ada log dari mesin ini.</p>
                </div>
            @endforelse
        </div>

        <div class="fingerprint-log-table hidden overflow-x-auto lg:block">
            <table class="table-readable min-w-full">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>PIN</th>
                        <th>Pegawai</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="fingerprint-log-table__time whitespace-nowrap">{{ $log->punched_at->format('d/m/Y H:i') }}</td>
                            <td class="fingerprint-log-table__pin">{{ $log->device_pin }}</td>
                            <td>{{ $log->employee->name ?? '-' }}</td>
                            <td>
                                @if($log->process_status === 'processed')
                                    <span class="fingerprint-log-table__status fingerprint-log-table__status--success">OK</span>
                                @elseif($log->process_status === 'failed')
                                    <span class="fingerprint-log-table__status fingerprint-log-table__status--failed">Gagal</span>
                                @else
                                    <span class="fingerprint-log-table__status">{{ $log->process_status }}</span>
                                @endif
                            </td>
                            <td class="fingerprint-log-table__note">{{ $log->process_message ?? ($log->attendance_id ? '#'.$log->attendance_id : '-') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="fingerprint-log-table__empty">Belum ada log dari mesin ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($logs->hasPages())
        <div>{{ $logs->links() }}</div>
    @endif
</div>
@endsection

@include('fingerprint-devices.partials.auto-refresh', [
    'pollUrl' => route('fingerprint-devices.log-sync-status', $fingerprintDevice),
    'logSyncSnapshot' => $logSyncSnapshot,
])

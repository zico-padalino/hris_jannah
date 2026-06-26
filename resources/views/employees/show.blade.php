@extends('layouts.app')

@section('title', $employee->name)
@section('subtitle', $employee->employee_number)

@section('content')
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
        <a href="{{ route('employees.index') }}" class="payroll-deduction-back shrink-0">
            <span class="payroll-deduction-back__icon" aria-hidden="true">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
            </span>
            <span>Kembali</span>
        </a>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('employees.edit', $employee) }}" class="btn-secondary">Edit</a>
            <a href="{{ route('faces.enroll', $employee) }}" class="btn-primary">Daftarkan Wajah</a>
            <a href="{{ route('employees.attendances', $employee) }}" class="btn-secondary">Riwayat Absensi</a>
        </div>
    </div>

    <div class="employee-detail-grid">
        <section class="panel employee-detail-panel">
            <h2 class="employee-detail-panel__title">Data Pegawai</h2>
            <div class="employee-info-grid">
                <div class="employee-info-tile">
                    <p class="employee-info-tile__label">Cabang</p>
                    <p class="employee-info-tile__value">{{ $employee->branch->name }}</p>
                </div>
                <div class="employee-info-tile">
                    <p class="employee-info-tile__label">Departemen</p>
                    <p class="employee-info-tile__value">{{ $employee->department->name ?? '—' }}</p>
                </div>
                <div class="employee-info-tile">
                    <p class="employee-info-tile__label">Jabatan</p>
                    <p class="employee-info-tile__value">{{ $employee->position->name ?? '—' }}</p>
                </div>
                <div class="employee-info-tile">
                    <p class="employee-info-tile__label">Shift Kerja</p>
                    <p class="employee-info-tile__value">{{ $employee->shiftLabel() }}</p>
                </div>
                <div class="employee-info-tile">
                    <p class="employee-info-tile__label">Status</p>
                    <p class="employee-info-tile__value">{{ ucfirst($employee->employment_status) }}</p>
                </div>
                <div class="employee-info-tile">
                    <p class="employee-info-tile__label">Gaji Pokok</p>
                    <p class="employee-info-tile__value">Rp {{ number_format($employee->base_salary, 0, ',', '.') }}</p>
                </div>
                <div class="employee-info-tile">
                    <p class="employee-info-tile__label">Bergabung</p>
                    <p class="employee-info-tile__value">{{ $employee->join_date?->format('d/m/Y') ?? '—' }}</p>
                </div>
                <div class="employee-info-tile">
                    <p class="employee-info-tile__label">PIN Fingerprint</p>
                    <p class="employee-info-tile__value">{{ $employee->fingerprint_pin ?? 'Belum diatur' }}</p>
                </div>
                <div class="employee-info-tile employee-info-tile--wide">
                    <p class="employee-info-tile__label">Email / Telepon</p>
                    <p class="employee-info-tile__value">{{ $employee->email ?? '—' }} · {{ $employee->phone ?? '—' }}</p>
                </div>
                @if($employee->address)
                    <div class="employee-info-tile employee-info-tile--wide">
                        <p class="employee-info-tile__label">Alamat</p>
                        <p class="employee-info-tile__value whitespace-pre-line">{{ $employee->address }}</p>
                    </div>
                @endif
                @if($employee->employment_status === 'contract' || $employee->contract_start_date || $employee->contract_end_date)
                    <div class="employee-info-tile employee-info-tile--wide">
                        <p class="employee-info-tile__label">Periode Kontrak</p>
                        <p class="employee-info-tile__value">
                            @if($employee->contract_start_date || $employee->contract_end_date)
                                {{ $employee->contract_start_date?->format('d/m/Y') ?? '—' }}
                                s/d
                                {{ $employee->contract_end_date?->format('d/m/Y') ?? '—' }}
                                @if($employee->contract_end_date)
                                    @php($daysLeft = now()->startOfDay()->diffInDays($employee->contract_end_date, false))
                                    <span @class([
                                        'ml-1 inline-flex rounded-full px-2 py-0.5 text-[0.6875rem] font-bold',
                                        'bg-red-100 text-red-800' => $daysLeft < 0,
                                        'app-status-pending' => $daysLeft >= 0 && $daysLeft <= 30,
                                    ]) @if($daysLeft > 30) style="background-color: var(--app-primary-soft); color: var(--app-primary-soft-text);" @endif>
                                        @if($daysLeft < 0)
                                            Berakhir {{ abs($daysLeft) }} h lalu
                                        @elseif($daysLeft === 0)
                                            Hari ini
                                        @else
                                            {{ $daysLeft }} h lagi
                                        @endif
                                    </span>
                                @endif
                            @else
                                —
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </section>

        <section class="panel employee-detail-panel">
            <h2 class="employee-detail-panel__title">Wajah Terdaftar ({{ $employee->faces->count() }})</h2>
            <div class="employee-face-panel">
                @forelse($employee->faces as $face)
                    <article class="employee-face-card">
                        @if($face->hasPhoto())
                            <img
                                src="{{ $face->photo_url }}"
                                alt="Wajah {{ $employee->name }}"
                                class="employee-face-card__avatar"
                            >
                        @else
                            <span class="employee-face-card__avatar employee-face-card__avatar--placeholder" aria-hidden="true">
                                {{ strtoupper(mb_substr($employee->name, 0, 1)) }}
                            </span>
                        @endif
                        <p class="employee-face-card__meta">{{ $face->enrolled_at->format('d/m/Y · H:i') }}</p>
                        @if($face->is_primary)
                            <span class="app-status-pending text-[0.6875rem]">Wajah utama</span>
                        @else
                            <span class="app-muted-text text-[0.6875rem] font-bold">Cadangan</span>
                        @endif
                        @perm('faces.enroll')
                            <form
                                method="POST"
                                action="{{ route('faces.destroy', [$employee, $face]) }}"
                                onsubmit="return confirm('Hapus data wajah ini? Pegawai perlu mendaftarkan ulang untuk absensi scan wajah.')"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="employee-face-card__delete">Hapus</button>
                            </form>
                        @endperm
                    </article>
                @empty
                    <div class="employee-face-empty">
                        <span class="employee-face-empty__icon" aria-hidden="true">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </span>
                        <p class="employee-face-empty__text">Belum ada wajah terdaftar.</p>
                        <a href="{{ route('faces.enroll', $employee) }}" class="btn-primary w-full text-center text-sm !min-h-10 !py-2">
                            Daftarkan Sekarang
                        </a>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    <section class="panel-table mt-4">
        <div class="border-b-2 px-4 py-3 sm:px-5" style="border-color: var(--app-border);">
            <h2 class="employee-detail-panel__title !mb-0">Absensi Terakhir</h2>
        </div>
        <table class="table-readable min-w-full">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Tipe</th>
                    <th>Lokasi</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employee->attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->attended_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $attendance->type->label() }}</td>
                        <td>{{ $attendance->branchLocation->name ?? '—' }}</td>
                        <td>@include('partials.attendance-status-badge', ['attendance' => $attendance])</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-sm font-semibold app-muted-text">Belum ada absensi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection

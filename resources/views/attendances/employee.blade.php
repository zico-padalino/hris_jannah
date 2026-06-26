@extends('layouts.app')

@section('title', 'Absensi — '.$employee->name)

@section('content')
    <div class="panel-table table-mobile-scroll">
        <table class="table-readable min-w-full">
            <thead class="bg-slate-50 text-left text-slate-500">
                <tr>
                    <th class="px-4 py-3">Waktu</th>
                    <th class="px-4 py-3">Cabang</th>
                    <th class="px-4 py-3">Lokasi</th>
                    <th class="px-4 py-3">Tipe</th>
                    <th class="px-4 py-3">Foto</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $attendance)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-3">{{ $attendance->attended_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $attendance->branch->name }}</td>
                        <td class="px-4 py-3">{{ $attendance->branchLocation->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $attendance->type->label() }}</td>
                        <td class="px-4 py-3">@include('partials.attendance-photo', ['attendance' => $attendance])</td>
                        <td class="px-4 py-3">@include('partials.attendance-status-badge', ['attendance' => $attendance])</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada absensi.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $attendances->links() }}</div>

@endsection

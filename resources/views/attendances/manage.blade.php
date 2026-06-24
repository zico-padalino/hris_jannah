@extends('layouts.app')
@section('title', 'Kelola Absensi')
@section('content')
<div class="mb-4 flex justify-end">
    <a href="{{ route('attendances.create') }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">+ Input Manual</a>
</div>

<div class="panel-table table-mobile-scroll">
    <table class="table-readable min-w-full">
        <thead class="bg-slate-50">
            <tr>
                <th class="px-4 py-3 text-left">Waktu</th>
                <th class="px-4 py-3 text-left">Pegawai</th>
                <th class="px-4 py-3 text-left">Foto</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Ubah Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $attendance)
                <tr class="border-t">
                    <td class="px-4 py-3">{{ $attendance->attended_at->format('d/m/Y H:i') }}</td>
                    <td class="px-4 py-3">{{ $attendance->employee->name }}</td>
                    <td class="px-4 py-3">@include('partials.attendance-photo', ['attendance' => $attendance])</td>
                    <td class="px-4 py-3">@include('partials.attendance-status-badge', ['attendance' => $attendance])</td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('attendances.status.update', $attendance) }}" class="flex gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="rounded border px-2 py-1 text-xs">
                                @foreach(['valid', 'late', 'invalid_face', 'invalid_location', 'invalid_both'] as $s)
                                    <option value="{{ $s }}" @selected($attendance->status->value === $s)>{{ \App\Enums\AttendanceStatus::from($s)->label() }}</option>
                                @endforeach
                            </select>
                            <button class="text-xs text-teal-700">Update</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada data absensi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $attendances->links() }}</div>

@include('partials.attendance-photo-modal')
@endsection

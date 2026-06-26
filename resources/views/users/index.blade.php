@extends('layouts.app')

@section('title', 'Pengguna')

@section('content')
    <div class="mb-6 flex justify-stretch sm:justify-end">
        @moduleAction('users', 'create')
            <a href="{{ route('users.create') }}" class="btn-primary w-full sm:w-auto">+ Tambah Pengguna</a>
        @endmoduleAction
    </div>

    <div class="panel-table overflow-x-auto">
        <table class="table-readable min-w-full">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Cabang</th>
                    <th>Status</th>
                    <th class="cell-actions-header">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td class="font-bold">{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->role->label() }}</td>
                        <td>{{ $user->branch->name ?? 'Pusat' }}</td>
                        <td>
                            @include('partials.active-status-badge', ['active' => $user->is_active])
                        </td>
                        <td class="cell-actions">
                            @include('partials.table-actions', [
                                'module' => 'users',
                                'edit' => route('users.edit', $user),
                            ])
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-10 text-center font-semibold text-slate-600">Belum ada pengguna.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
@endsection

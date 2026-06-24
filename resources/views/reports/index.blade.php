@extends('layouts.app')
@section('title', 'Laporan Absensi')
@section('content')
<form method="GET" class="mb-4"><input type="month" name="month" value="{{ $month }}" class="rounded-lg border px-3 py-2 text-sm"><button class="ml-2 rounded-lg bg-slate-800 px-4 py-2 text-sm text-white">Filter</button></form>
<div class="overflow-hidden rounded-xl border bg-white shadow-sm"><table class="table-readable min-w-full"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Cabang</th><th class="px-4 py-3 text-left">Total</th><th class="px-4 py-3 text-left">Valid</th><th class="px-4 py-3 text-left">Invalid</th></tr></thead><tbody>
@forelse($summary as $row)<tr class="border-t"><td class="px-4 py-3">{{ $row->branch->name ?? 'Cabang #'.$row->branch_id }}</td><td class="px-4 py-3">{{ $row->total }}</td><td class="px-4 py-3 text-emerald-700">{{ $row->valid_count }}</td><td class="px-4 py-3 text-red-600">{{ $row->invalid_count }}</td></tr>@empty<tr><td colspan="4" class="px-4 py-8 text-center text-slate-500">Tidak ada data.</td></tr>@endforelse
</tbody></table></div>
@endsection

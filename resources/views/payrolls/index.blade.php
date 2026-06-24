@extends('layouts.app')
@section('title', 'Payroll')
@section('content')
<form method="POST" action="{{ route('payrolls.store') }}" class="mb-6 flex flex-wrap gap-3 rounded-xl border bg-white p-4 shadow-sm">@csrf
<select name="branch_id" class="rounded-lg border px-3 py-2 text-sm"><option value="">Semua Cabang</option>@foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach</select>
<input name="month" type="number" min="1" max="12" value="{{ now()->month }}" class="rounded-lg border px-3 py-2 text-sm w-24">
<input name="year" type="number" value="{{ now()->year }}" class="rounded-lg border px-3 py-2 text-sm w-28">
<button class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Buat Payroll</button></form>
<div class="overflow-hidden rounded-xl border bg-white shadow-sm"><table class="table-readable min-w-full"><thead class="bg-slate-50"><tr><th class="px-4 py-3 text-left">Periode</th><th class="px-4 py-3 text-left">Cabang</th><th class="px-4 py-3 text-left">Status</th><th></th></tr></thead><tbody>
@foreach($periods as $period)<tr class="border-t"><td class="px-4 py-3">{{ $period->name }}</td><td class="px-4 py-3">{{ $period->branch->name ?? 'Semua' }}</td><td class="px-4 py-3">{{ $period->status->label() }}</td><td class="px-4 py-3 text-right"><a href="{{ route('payrolls.show', $period) }}" class="text-teal-700">Detail</a></td></tr>@endforeach
</tbody></table></div><div class="mt-4">{{ $periods->links() }}</div>
@endsection

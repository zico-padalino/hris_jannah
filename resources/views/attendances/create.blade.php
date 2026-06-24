@extends('layouts.app')
@section('title', 'Input Absensi Manual')
@section('content')
<div class="mx-auto max-w-xl rounded-xl border bg-white p-6 shadow-sm"><form method="POST" action="{{ route('attendances.manual.store') }}" class="space-y-4">@csrf
<select name="employee_id" required class="w-full rounded-lg border px-3 py-2">@foreach($employees as $e)<option value="{{ $e->id }}">{{ $e->name }}</option>@endforeach</select>
<select name="branch_id" required class="w-full rounded-lg border px-3 py-2">@foreach($branches as $b)<option value="{{ $b->id }}">{{ $b->name }}</option>@endforeach</select>
<select name="type" class="w-full rounded-lg border px-3 py-2"><option value="check_in">Masuk</option><option value="check_out">Pulang</option></select>
<input name="attended_at" type="datetime-local" required class="w-full rounded-lg border px-3 py-2">
<select name="status" class="w-full rounded-lg border px-3 py-2"><option value="valid">Valid</option><option value="invalid_face">Invalid Wajah</option><option value="invalid_location">Invalid Lokasi</option><option value="invalid_both">Invalid Keduanya</option></select>
<textarea name="notes" rows="2" class="w-full rounded-lg border px-3 py-2" placeholder="Catatan"></textarea>
<button class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Simpan</button></form></div>
@endsection

@extends('layouts.app')
@section('title', 'Tambah Pengguna')
@section('content')
<div class="mx-auto max-w-xl rounded-xl border bg-white p-6 shadow-sm">
<form method="POST" action="{{ route('users.store') }}" class="space-y-4">@csrf
@include('users._form')
<button class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Simpan</button>
</form></div>
@endsection

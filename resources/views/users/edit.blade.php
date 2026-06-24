@extends('layouts.app')
@section('title', 'Edit Pengguna')
@section('content')
<div class="mx-auto max-w-xl rounded-xl border bg-white p-6 shadow-sm">
<form method="POST" action="{{ route('users.update', $user) }}" class="space-y-4">@csrf @method('PUT')
@include('users._form')
<button class="rounded-lg bg-teal-700 px-4 py-2 text-sm text-white">Update</button>
</form></div>
@endsection
